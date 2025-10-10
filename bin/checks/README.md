# Commandline checks for Platform.sh projects

Until the checks are migrated fully into project 'Metrics` entities,
we will prototype the check process by enwrapping these already-existing bash scripts that perform the same functions.

A check found in the `bin/audits` directory is a check for a specific project status.

Checks usually require some sort of prerequisite environment variables to be set,
such as the project URL, or an API key.
These variables are expected to be set in the environment before running the script.
This way, any number of named variables are available.
Passing them as ordered arguments to the script is more problematic to do generically.

The script should return an exit code indicating whether the checks ran clean or if there were problems.

STDOUT becomes the data that may be recorded by the system as additional information about the response.
Normally it should contain a single value (ie, response code, or service ID) but may be structured as JSON for more complex responses.

STDERR may be recorded for additional information about the check process.



