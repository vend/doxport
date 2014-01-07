# Doxport

Doxport is a library to mix Doctrine and a bit of graph theory to get stuff done.
Specifically, it aims to give you powerful exporting and archiving tools for
a relational Doctrine2 schema.

Doxport uses Doctrine 2.4, [Clue/Graph](https://github.com/clue/graph), and
Symfony components. It's PHP 5.4+, and at this stage, probably a bit MySQL-only although
the guts should be solid.

Given a relational schema, usually via class annotations, Doxport should let you
safely archive partial data from related tables, in the correct order.

## Mechanism

You nominate a root entity, and some set of criteria to query against its table. Doxport sets about finding the best way to export or archive related data.

1. We consider a directed graph, where the vertices are the entities involved
in the operation, and every relation between them is an edge. 
2. The edge goes
from the table with the foreign key to the table with the referenced key in each relation,
and represents a dependency; the foreign-key table will need to be cleared before
the row in the referenced table. 
3. We don't add edges for relations already covered
by an onDelete clause. Those should be taken care of by your database. (TODO)
4. We verify this graph is a DAG.
5. We take a topological ordering of this graph, which gives us a valid order to
process the tables in.
6. For each entity, we find the shortest path between the target table and the root entity you nominated, only considering relations covered by an index. (At some point, this should follow the minimum spanning tree of the cardinality of the covering indexes - TODO)
7. We produce an SQL query that inner joins the target table to the root, apply your original criteria, and iterate through the results

What happens next to the resulting entities varies.

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

## Actions

* Export
  * Currently writes serialised entities to CSV. Some obvious problems with that. Migrations not least of all. (TODO)
* Delete
  * Writes the entities to a file, then queues up their deletion. Once a suitable chunk has been processed, the file is flushed and sync'd, then the deletions are committed.