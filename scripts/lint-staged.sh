#!/bin/bash

if [ -x "$(command -v vagrant)" ]; then
  # Vagrant is installed
  vagrant status --machine-readable | grep -q 'state,running'
  if [ $? -eq 0 ]; then
    # Vagrant is used and up. Run the same script from within Vagrant.
    # "-- -T" suppresses the 'Connection to 127.0.0.1 closed.' message after the SSH command finishes.
    vagrant ssh -c 'cd /vagrant && bash scripts/lint-staged.sh' -- -T
  else
    # Vagrant is not running or not used
    # Skip running lint-staged
    echo "Vagrant is installed but doesn't seem to be used or up. Skipping code formatting."
    exit 0
  fi
else
  # We're inside vagrant or on Gitpod. Simply run lint-staged.
  $(npm bin)/lint-staged
fi