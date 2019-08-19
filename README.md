# find-composer-orphans

A simple script to find orphaned packages that still live in `composer.lock` while no longer being
required from `composer.json`.

Call me using `php runner.php composer.json composer.lock` and you will see the list of orphans.
