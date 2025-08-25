#!/bin/bash

# Check if any arguments are passed
if [ -z "$1" ]; then
  echo "Usage: $0 'command_to_run'"
else
  # Execute the command passed in quotes
  eval "$1"
fi