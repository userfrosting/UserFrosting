UserCake implements authorization/access control at the page level.  Each user group could be granted permissions for any number of pages.  This works well if each page performs a single activity that is independent of context.

However, sometimes you want different users to be authorized to do different things on the same page.  For example, suppose you create a new type of user, "group admins".  Group admins have administrative control, but only over one specific group.  If Alice is a group admin for her group (let's call it `Alice's peeps`), you want her to be able to create, delete, and update users in her own group but not in someone else's group (`Bob's peeps`).

Instead of having a separate API page (such as `update_user.php`) for `Alice's peeps` and `Bob's peeps`, we have a single page that checks to make sure that the current user is authorized to perform a specific action with specific parameters.  Informally, we want to say that "Alice has permission for action: "update user", with restriction "can only update users in group 'Alice's peeps'".  Formally, we'd specify this in the `user_action_permits` table, as such:

| id  | user_id | action | permits |
| ------------- | ------------- | ------------- | ------------- |
| 9 | 7 | updateUser | sameGroup(user_id) |

This tells UserFrosting that whenever Alice (user id 7) wants to update another user, the system should first check that Alice and the target user are in the same group, using the permission validation function `sameGroup()` (defined in `models/authorization.php`).  The parameter `user_id` in `sameGroup()` tells UserFrosting that it should attempt to match the parameter with the same name `user_id` that is being passed into `updateUser`.

Nifty, eh?
