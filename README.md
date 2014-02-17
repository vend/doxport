# Doxport

Doxport is a library to mix Doctrine and a bit of graph theory to get stuff done.
Specifically, it aims to give you powerful exporting and archiving tools for
a relational Doctrine2 schema.

Doxport uses Doctrine 2.4, [Clue/Graph](https://github.com/clue/graph), and
Symfony components. It's PHP 5.4+, and at this stage, probably a bit MySQL-only although
the guts should be solid.

Given a relational schema, usually via class annotations, Doxport should let you
safely archive, delete or import partial data from related tables, in the correct order.

## Algorithm

### Graph Theory

Doxport uses a bit of graph theory, so you might want to read up on these:

* [Directed acyclic graphs](http://en.wikipedia.org/wiki/Directed_acyclic_graph): DAGs for short
* [Topological sorting](http://en.wikipedia.org/wiki/Topological_sorting): basically, produces a dependency-safe order to walk the nodes of a DAG

### Outline

To start with, you tell Doxport the *root entity type*, and give it some criteria to apply to this type. For example, you might say the root entity is `My\Namespace\User`, and the criteria is `id = <some uuid>`.

Then, Doxport will:

1. Create a DAG with all tables in the database as the nodes, and all associations between them as the edges
2. Filter the edges to only consider 'constraining associations'
   * Usually this means only associations that actually own the foreign key, and have a foreign key constraint
3. Filter the nodes to only consider tables still associated with the root entity after the edge filtering
   * This step removes irrelevant tables. For example, if your root entity is the retailer, the admin user tables would be filtered out.
4. Produce a topological sort of this DAG
5. For each node, in the order of the topological sort, find the shortest path back to the root entity, using a slightly different filtered DAG
   * The filtering is slightly different for this DAG because we additionally only consider edges that are 'covered' by an index

Each time step 5 occurs, an action is invoked on the resulting query. This might be `export` or `delete`, for example.

## Configuration

Doxport follows a similar configuration approach to Doctrine2 (when installed
as a composer project; Symfony2 does this set up for you). You're expected to
create a file named `cli-config.php` or `config/cli-config.php`. This PHP file
should return a HelperSet, which can be produced from an EntityManager via Doctrine2's ConsoleRunner:

```php
return ConsoleRunner::createHelperSet($entityManager);
```

See [Doctrine2's configuration and installation](http://docs.doctrine-project.org/en/latest/reference/configuration.html) guide, and particularly the chapter on CLI tool setup, for more information.

Once you've done that, Doxport runs as a Composer bin script.

## Output

### Directory

The delete and export commands produce output to a directory. By default, this is `build/<action>/<criteria>_<value>`. So, for example, you might end up with an output directory like: `build/delete/id_5eb62e62-473b-11e3-a766-080027f3add4`. You can change the location of the output with the `--data-dir` option.

### Files

Within the output directory, you'll find a file for each entity type. There are also a few additional files:

* constraints.png: A graphviz image of the constraint graph, useful for debugging the produced DAG
* constraints.txt: The topological order the entities were dumped in (on entity per line). Used (in reverse) to decide the order to import.

### Format

By default, Doxport will output to JSON format. This can be changing using the `--format` option. The JSON format is an array of objects.