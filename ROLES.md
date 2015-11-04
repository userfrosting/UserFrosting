# Role-based Access Control in v0.3.2

## Authorization Rules

Authorization rules are used to determine a user's permission at specific checkpoints in the code, called authorization hooks.  An authorization rule assigns permission for a specific authorization hook to a specific role.  It may also optionally set conditions for this rule, which must be met before access can be granted.  These conditions can be any boolean expression composed of method calls to **access conditions**, which are defined in `models/auth/AccessCondition`.  The parameters for these methods can be numbers or string literals, or they can be variables taken from the current authenticated user object (self.*), the current route's GET parameters (route.*), or any other parameters passed as an associative array into the call to `checkAccess`.

## Roles

Roles are ways of grouping authorization rules.  This makes it easier to assign the same authorization rules to many users at once.  In past versions, these were (confusingly) referred to as "groups". 