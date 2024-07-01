#!/bin/sh

# apt install inotify-tools

while inotifywait -qre modify "$PWD/src" "$PWD/test"; do
    clear && vendor/bin/phpunit
done