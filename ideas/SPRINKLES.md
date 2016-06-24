- sprinkles
  - core
    - assets
    - routes
    - schema
    - src
    - templates
    - composer.json
    - sprinkle.json
  - account
    - assets
    - routes
    - schema
    - src    
    - templates
    - composer.json    
    - sprinkle.json   
  - nyx
    - assets
    - templates
    - composer.json    
    - sprinkle.json    
  

sprinkle.json tells UF about each sprinkle and its dependencies (on other sprinkle, for example)

```
// Sets up the Slim app and loads the core UF dependencies, routes, template directory, etc
$uf = new UserFrosting();

// Loads the desired sprinkles, recursively loading any dependencies they have that aren't already loaded
$uf->load([
    "account",
    "nyx"
]);

// Calls the Slim app's run() method
$uf->run();

```

I don't see sprinkles calling any methods other than the one to register a custom services container
so, this part: https://github.com/userfrosting/bones/blob/master/public/index.php#L15-L20 for each sprinkle's service provider
anything that needs to be run immediately can be placed in the DI container
and there will be some listing of init services in sprinkle.json
and since DI dependencies are automatically run when first invoked, we won't need any additional hook system for specifying the run order
dependencies will get resolved as needed by Pimple :grinning: