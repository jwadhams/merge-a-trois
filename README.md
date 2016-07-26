# merge-a-trois
Three-way merge for PHP array data

I have a PHP-backended application that serves up a large JSON-encoded unstructured blob of data to JavaScript clients.  The clients manipulate bits inside that big object, then return the whole altered object to the back end, possibly minutes later.

It's possible that two people can be collaborating on the same big object roughly-synchronously (typically on different bits inside the large object), so the backend needs a way to merge those changes.

The goal of this library is to silently merge changes and end up with one authoritative server-stored object that as much as possible honors *all* collaborators.  Unlike software version control merges, no user is in a place to arbitrate conflicts, so the algorithm *always* returns an answer, and we keep audit trails outside of this library.

When in doubt, the most recent change wins.

## Why three-way merge?

Each client is first pulling a known-good version of the object. The three-way merge technique lets us use a common ancestor to the potentially conflicting changes, and is especially useful in noticing deleted content.

This library started life as a port of the CoffeeScript library [3-way-merge](https://github.com/falsecz/3-way-merge)
