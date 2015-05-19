<?php

/*******************

DO NOT CHANGE!  This is a core UserFrosting file, and should not need to be changed by developers.

********************/

namespace UserFrosting;

use PhpParser\Node;

class Authorization {

    static public function checkAccess($hook, $params){
        $app = \Slim\Slim::getInstance();
        
        // The master (root) account has access to everything.
        if ($app->user->id == $app->config('user_id_master'))
            return true;
             
        // Try to find an authorization rule for $hook that matches the currently logged-in user, or one of their groups.
        $rule = AuthLoader::fetchUserAuthHook($app->user->id, $hook);
        
        if (empty($rule))
            $pass = false;
        else {      
            $ace = new AccessConditionExpression($app);
            $pass = $ace->evaluateCondition($rule['conditions'], $params);
        }
        
        // If no user-specific rule is passed, look for a group-level rule
        if (!$pass){
            $ace = new AccessConditionExpression($app);
            $groups = $app->user->getGroups();
            foreach ($groups as $group){
                // Try to find an authorization rule for $hook that matches this group
                $rule = AuthLoader::fetchGroupAuthHook($group->id, $hook);
                if (!$rule)
                    continue;
                $pass = $ace->evaluateCondition($rule['conditions'], $params);
                if ($pass)
                    break;
            }
        }
        return $pass;
    }
}

// Models the evaluation of an authorization condition expression, which is built as a boolean expression composed of AccessCondition method calls.
class AccessConditionExpression {

    protected $_app;        // The framework app to use (default Slim)
    protected $_parser;
    protected $_traverser;
    protected $_prettyPrinter;
    protected $_debug;

    public function __construct($app, $debug = false){
        $this->_parser        = new \PhpParser\Parser(new \PhpParser\Lexer\Emulative);
        $this->_traverser     = new \PhpParser\NodeTraverser;
        $this->_prettyPrinter = new \PhpParser\PrettyPrinter\Standard;
        $this->_debug = $debug;
        $this->_app = $app;
    }

    // Evaluates a condition expression, based on the given parameters.  Returns true if the condition is passed for the given parameters, otherwise returns false.
    public function evaluateCondition($condition, $params){
        // Set the reserved `self` and `route` parameters
        $params['self'] = $this->_app->user->export();
        
        $route = $this->_app->router()->getCurrentRoute();
        $params['route'] = $route->getParams();
        
        /* Traverse the parse tree, and execute all function calls as methods of class AccessCondition.
           Replace the function node with the return value of the method.
        */
        $pv = new \ParserNodeFunctionEvaluator($params);
        $this->_traverser->addVisitor($pv);
        
        $code = "<?php $condition;";
        
        if ($this->_debug){
            echo "<pre>Evaluating access conditions:\n";
            echo htmlentities($condition). "\n\n".
            "on params: \n" .
            print_r($params, true) . "\n" .
            "</pre>";
        }
        
        try {
            
            // parse
            $stmts = $this->_parser->parse($code);    
            
            // traverse
            $stmts = $this->_traverser->traverse($stmts);
        
            // Evaluate boolean statement.  It is safe to use eval() here, because our expression has been reduced entirely to a boolean expression.
            $expr = $this->_prettyPrinter->prettyPrintExpr($stmts[0]);
            $expr_eval = "return " . $expr . ";\n";
            $result = eval($expr_eval);
            
            if ($this->_debug){
                echo "<pre>\"$expr\" evaluates to " . ($result == true ? "true" : "false") . "</pre>";
            }
            
            return $result;
        } catch (\PhpParser\Error $e) {
            throw new \Exception("Error parsing access condition \"$condition\": \n" . $e->getMessage());
        }
    }

}
