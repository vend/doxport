# Doxport

Doxport is a library to mix Doctrine and a bit of graph theory to get stuff
done.  Specifically, it aims to give you powerful **exporting and archiving
tools for a relational Doctrine2 schema**.

Doxport uses Doctrine 2.4, [Clue/Graph](https://github.com/clue/graph), and
Symfony components. It's PHP 5.4+ and, at this stage, probably a bit
MySQL-specific (although the guts should be solid).

Given a relational schema, usually via class annotations, Doxport should let
you safely **archive, delete or import partial data from related tables**, in
the correct order.

[![Build Status](https://travis-ci.org/vend/doxport.png)](https://travis-ci.org/vend/doxport)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/vend/doxport/badges/quality-score.png?s=babbc605acfb81f0cf141b93fa14f2b1bb05a361)](https://scrutinizer-ci.com/g/vend/doxport/)
[![Code Coverage](https://scrutinizer-ci.com/g/vend/doxport/badges/coverage.png?s=b19bb9d0469a3fc01d0894a3b2f7dc3522176bdc)](https://scrutinizer-ci.com/g/vend/doxport/)

## Installation

Doxport is installed via Composer. The package name is `vend/doxport`.

## Actions

### Delete

The delete action removes rows from the database, and saves their content to
the output directory. If the **eio** extension is loaded, these files will be
sync'd at the point the entity manager is flushed and the rows actually
removed.

### Export

Like the delete action, the export action saves row content to the output
directory. However, it does not remove rows from the database. This means you
won't be able to immediate import a set of exported data into the same
database, because you'll get primary key errors. However, you can import into a
fresh database, and this action is very useful for taking backups without
disturbing the original data.

### Import

The import action takes a path to a previously output set for data (usually
from the export or delete actions). By reading the dumped constraint
information, Doxport figures out the correct order to import the data, and does
so.

## Algorithm

Doxport uses a bit of graph theory, so you might want to read up on these
before jumping in:

* [Directed acyclic
  graphs](http://en.wikipedia.org/wiki/Directed_acyclic_graph): DAGs for short
* [Topological sorting](http://en.wikipedia.org/wiki/Topological_sorting):
  basically, produces a dependency-safe order to walk the nodes of a DAG

### Outline

To start with, you tell Doxport the *root entity type*, and give it some
criteria to apply to this type. For example, you might say the root entity is
`My\Namespace\User`, and the criteria is `id == <some value>`.

Then, Doxport will:

1. Create a DAG with all tables in the database as the nodes, and all
   associations between them as the edges
2. Filter the edges to only consider 'constraining associations'
   * Usually this means only associations that actually own the foreign key,
     and have a foreign key constraint
3. Filter the nodes to only consider tables still associated with the root
   entity after the edge filtering
   * This step removes irrelevant tables. For example, only tables associated with the user type (via any number of relations)
4. Produce a topological sort of this DAG
5. For each node, in the order of the topological sort, find the shortest path
   back to the root entity, using a slightly different filtered DAG
   * The filtering is slightly different for this DAG because we additionally
     only consider edges that are 'covered' by an index

Each time step 5 occurs, an action is invoked on the resulting query. This
might be `export` or `delete`, for example.

## Configuration

Doxport follows a similar configuration approach to Doctrine2 (when installed
as a composer project; Symfony2 does this set up for you). You're expected to
create a file named `cli-config.php` or `config/cli-config.php`. This PHP file
should return a `HelperSet`, which can be produced from an `EntityManager` via
Doctrine2's `ConsoleRunner`:

```php
return ConsoleRunner::createHelperSet($entityManager);
```

See [Doctrine2's configuration and
installation](http://docs.doctrine-project.org/en/latest/reference/configuration.html)
guide, and particularly the chapter on CLI tool setup, for more information.

Once you've done that, Doxport runs as a Composer bin script:

```
$ ./bin/doxport help
Doxport relational data tool version 0.0.1

Usage:
  [options] command [arguments]

Options:
  --help           -h Display this help message.
  --quiet          -q Do not output any message.
  --verbose        -v|vv|vvv Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
  --version        -V Display this application version.
  --ansi              Force ANSI output.
  --no-ansi           Disable ANSI output.
  --no-interaction -n Do not ask any interactive question.

Available commands:
  delete   Deletes a set of data from the database
  export   Exports a set of data from the database
  help     Displays help for a command
  import   Imports a set of exported data into the database
  list     Lists commands
```

### Excluding Associations

Sometimes, you may want to exclude a relation or entity type from being
considered for Doxport operations. You can do this using annotations. All these
annotations are in the `Doxport\Annotation` namespace.

Name | Type | Use
-----|------|-----
`Shared` | Entity | To exclude an entity completely, as if the entity is shared, and shouldn't be considered owned by any relation that links to it.
`Exclude` | Property | To exclude a single relation from consideration. Useful for breaking cycles in the constraint graph, where entity A relates to B *and* B relates back to A via a different relation.
`Clear` | Property | Before a delete operation, these columns will be set to null on the entity, and that change persisted. The existing values will be written to the output directory. After an import operation, these saved values will be updated back onto the entity.

To use these annotations, you'll first need to register the annotation namespace with the registry. You can do that like this:

```php
AnnotationRegistry::registerAutoloadNamespaces([
    'Doxport\Annotation' => 'path/to/Doxport'
]);
```

The path to use is the relative path to the root Doxport namespace. If you don't know where this is, you can use a Composer autoloader instance to help find it.

```php
$loader = require 'vendor/autoload.php'; # Composer autoloader

AnnotationRegistry::registerAutoloadNamespaces([
    'Doxport\Annotation' => $loader->getPrefixes()['Doxport\\'][0]
]);
```

## Output

### Directory

By default, the output directory is is `build/<action>/<criteria>_<value>`. You
can change the location of the output with the `--data-dir` option.

### Files

Within the output directory, you'll find a file for each entity type. There are
also a few additional files:

* constraints.png: A graphviz image of the constraint graph, useful for
  debugging the produced DAG
* constraints.txt: The topological order the entities were dumped in (one entity type per line). Used (in reverse) to decide the order to import.

### Format

By default, Doxport will output to JSON format. This can be changing using the
`--format` option. The JSON format is an array of objects.

