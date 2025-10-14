# CLI Checks for Platform.sh Projects

## Motivation

For atomic simplicity and reusability, checks are implemented as small, independent bash scripts. Each check focuses on one specific aspect of a project.

**Why bash scripts instead of library calls?**
- **Portable**: Easy to share, test, and run independently
- **Transparent**: The actual commands (curl, grep, platform CLI) are visible and documentable
- **Context-free**: No dependencies on PHP classes, frameworks, or application state
- **Reusable**: Shared between this Drupal project and other audit tooling (including Gherkin test suites)

For example, checking cache headers could be done via an HTTP client library, or simply via `curl` + `grep`. The latter is more transparent and easier to reason about.

## Architecture

### Check Files (`bin/checks/check_*`)

Each check file is a bash script that:
1. **Declares metadata** about what it checks
2. **Defines required environment variables** it expects
3. **Implements a `check()` function** that performs the actual test

Check files are sourced (not executed directly) so metadata can be examined without triggering the check.

### The Check Wrapper (`bin/run_check`)

The `run_check` wrapper script:
1. Sources the specified check file from `bin/checks/`
2. Validates required environment variables are set
3. Executes the check's `check()` function
4. Captures exit code, STDOUT, and STDERR
5. Logs results to SQLite audit database (optional, for non-Drupal contexts)
6. Returns the check's exit code and output

**Usage:**
```bash
export PLATFORM_PROJECT="myproject123"
./bin/run_check check_project_organization
```

## Check File Contract

Each check file in `bin/checks/` should define:

| Variable | Purpose | Required |
|----------|---------|----------|
| `CHECK_ID` | Unique identifier for this check | Yes |
| `CHECK_DESCRIPTION` | Human-readable description | Yes |
| `REQUIRED_PARAMETERS` | Array of env vars that must be set | Yes |
| `literal_check_command` | Display string showing what will run | Optional |
| `CHECK_RETURN_VARNAME` | Name for the returned value (metadata) | Optional |
| `check()` function | Bash function that performs the actual test | Yes |

### Environment Variables

Checks receive all input via **environment variables**, not positional arguments. This allows:
- Any number of named parameters
- Flexibility in what each check needs
- Clear declaration of dependencies via `REQUIRED_PARAMETERS`

Common environment variables:
- `PLATFORM_PROJECT` - Platform.sh project ID
- `PLATFORM_ENVIRONMENT` - Environment name
- `PROJECT_URL` - The URL to test
- `PLATFORMSH_API_TOKEN` - API authentication

### Return Values

**Exit codes:**
- `0` = OK/Success
- `1` = Warning
- `2` = Error/Failure
- `3+` = Check-specific status codes

**STDOUT:** The check result value
- Should be a single value (e.g., response code, service ID)
- May be structured as JSON for complex responses
- Captured and stored as the `data` field in Drupal metrics

**STDERR:** Diagnostic logging
- Progress messages, intermediate results
- Captured and may be stored as `note` field in Drupal metrics

## Example Check File

```bash
#!/usr/bin/env bash
CHECK_ID="check_site_online"
CHECK_DESCRIPTION="Verify the site responds with HTTP 200"
REQUIRED_PARAMETERS=(PROJECT_URL)
literal_check_command="curl -I \$PROJECT_URL"

check() {
  response_code=$(curl -s -o /dev/null -w "%{http_code}" "$PROJECT_URL")
  echo "$response_code"

  if [ "$response_code" = "200" ]; then
    return 0  # OK
  else
    return 2  # Error
  fi
}
```

## Integration with Drupal

The PHP class `CliCheck` wraps this system:
1. Sets required environment variables from Drupal context
2. Executes `bin/run_check <check_name>`
3. Parses exit code, STDOUT, STDERR
4. Returns status for storage in Metric entities

## Audit Logging

When run outside Drupal, checks log to an SQLite database via `audit_reporting.lib`. This provides lightweight persistence (one check = one row) useful for standalone audit scripts. Within Drupal, this is superseded by Metric entities.

## Additional Metadata (Gherkin)

Check files may define additional metadata for integration with Gherkin/Behat test suites in other projects:
- `CHECK_WHEN` - Gherkin step matcher pattern

This metadata is ignored by the Drupal integration but allows checks to be reused as test steps.


