# Senior Review -- Required Improvements

## Goal

This document lists the recommended improvements before submitting the
solution. The focus is correctness, architecture, maintainability and
alignment with the test assignment.

------------------------------------------------------------------------

# 1. Fix the versioning contract documentation (HIGH)

## Problem

The current comments suggest that explicit version recording *does not
silently miss bulk updates*.

This is technically incorrect.

The following code still bypasses versioning:

``` php
Company::query()->where(...)->update([...]);
```

or

``` php
$company->save();
```

when `VersionManager` is not used.

## Required change

Update all comments and README to clearly state:

> Version recording is guaranteed only through the VersionManager write
> path. Direct Eloquent saves and Query Builder bulk updates are
> intentionally outside the supported contract.

------------------------------------------------------------------------

# 2. Document the write-path contract (HIGH)

Add a dedicated README section.

Example:

``` text
All modifications of Versionable models MUST go through VersionManager.

Do not call:

$model->save()
$model->update(...)
Model::query()->update(...)

outside VersionManager.
```

This prevents accidental history corruption.

------------------------------------------------------------------------

# 3. Add request normalization (HIGH)

Before validation:

``` php
trim(name)
trim(edrpou)
trim(address)
```

Reason:

The assignment example already contains leading whitespace in the
address.

Without normalization unnecessary versions can be created.

------------------------------------------------------------------------

# 4. Clarify behaviour of non-versioned attributes (HIGH)

Current behaviour:

If only a non-versioned attribute changes:

-   model is NOT saved
-   operation becomes Duplicate

Decide one explicit contract:

Option A (recommended)

-   all persisted attributes must also be versioned

OR

Option B

-   persist every attribute
-   create versions only when tracked attributes change

Document this decision.

------------------------------------------------------------------------

# 5. Improve VersionManager naming (MEDIUM)

Current name:

    VersionManager

Actually performs:

-   lookup
-   locking
-   upsert
-   duplicate detection
-   persistence
-   retry
-   version creation

Recommended:

    VersionedUpsertService

or split into

    VersionRecorder
    VersionedModelWriter

Current implementation is acceptable for the assignment but naming can
better reflect responsibility.

------------------------------------------------------------------------

# 6. Add snapshot integrity tests (HIGH)

Missing tests:

-   Version #1 keeps original values after updates.
-   Version #2 stores new values.
-   Previous snapshots never change.

------------------------------------------------------------------------

# 7. Add duplicate timestamp test (MEDIUM)

Verify that duplicate requests do NOT modify:

    updated_at

Assignment requirement:

> nothing should be updated.

------------------------------------------------------------------------

# 8. Add transaction rollback test (HIGH)

Create a test proving:

If version insertion fails:

-   company update is rolled back
-   no partial persistence exists

This proves atomicity.

------------------------------------------------------------------------

# 9. Add whitespace normalization test (MEDIUM)

Verify:

    " Kyiv"

and

    "Kyiv"

produce Duplicate instead of Updated.

------------------------------------------------------------------------

# 10. Clarify framework dependency (LOW)

Current comments imply framework independence.

Implementation directly depends on:

-   Eloquent
-   DB facade
-   Morph relations

Update comments to:

> Framework-aware Laravel application layer.

Avoid claiming framework independence.

------------------------------------------------------------------------

# 11. Make Version immutable (LOW)

Prevent:

``` php
$version->update(...)
$version->delete()
```

Possible solution:

Throw exceptions from updating/deleting model events.

History should be append-only.

------------------------------------------------------------------------

# 12. Review retry behaviour (LOW)

Current retry catches every unique constraint violation.

Prefer retry only for:

-   lookup unique key collision during create

Avoid retrying unrelated unique indexes.

------------------------------------------------------------------------

# 13. Consider JSON portability (LOW)

Current migration:

``` php
jsonb()
```

If PostgreSQL is mandatory:

-   keep jsonb
-   mention PostgreSQL in README.

Otherwise:

    json()

improves database portability.

------------------------------------------------------------------------

# 14. Strengthen README

Document:

-   architecture
-   transaction guarantees
-   version numbering
-   snapshot strategy
-   concurrency handling
-   explicit write path
-   unsupported operations (bulk update)

------------------------------------------------------------------------

# 15. Keep explicit versioning

Do NOT migrate to automatic Eloquent events unless explicitly required.

Current explicit VersionManager approach is acceptable and has
advantages:

-   visible write flow
-   transaction control
-   deterministic behaviour
-   easier testing

The only requirement is documenting the write-path contract clearly.

------------------------------------------------------------------------

# Overall assessment

Current solution is approximately **8.5--9/10**.

After applying the improvements above it becomes a strong senior-level
implementation suitable for submission.
