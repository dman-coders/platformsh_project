# Commandline checks for Platform.sh projects

For atomic simplicity, it is helpful to have a number of small scripts that each check one thing about a project.
Thus, specific one-liners can be documented and tested, independent of the surrounding audit framework.

If I wanted to request an address and check its cache-response heder, this COULD be done by opening a
HTTP Request Session and invoking that.
Or, we could just run `curl` and `grep` (or `print-result`).

The second method is easier to share and removes a lot of dependence on context and tooling.
I have built this tooling anyway, but at ist heart, as much as possible,
it runs these basic commands and reports on them - not on the response from a library.

The `check` script found here enwrapps these already-existing bash scripts that perform the same functions.

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



