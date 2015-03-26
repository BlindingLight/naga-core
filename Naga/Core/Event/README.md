#Currently available events

##Auth

```
auth.beforeLogin            fires when user logs in (before user data is stored in session)
							parameters passed by reference to listener callback
							see Auth::loginAs() for parameter list
auth.afterLogin             fires when user logs in (after user data is stored in session)
							parameters are NOT passed by reference to listener callback
							see Auth::loginAs() for parameter list
```