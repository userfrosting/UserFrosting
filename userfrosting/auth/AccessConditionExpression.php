<?php

/*******************

DO NOT CHANGE!  This is a core UserFrosting file, and should not need to be changed by developers.

********************/

namespace UserFrosting;

use PhpParser\Node;

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
        // Set the reserved `self` and `route` parameters.
        // This replaces any values of `self` or `route` specified in the arguments, thus preventing them from being overridden in malicious user input.
        $params['self'] = $this->_app->user->export();
        
        $route = $this->_app->router()->getCurrentRoute();
        $params['route'] = $route->getParams();
        
        /* Traverse the parse tree, and execute all function calls as methods of class AccessCondition.
           Replace the function node with the return value of the method.
        */
        $pv = new \ParserNodeFunctionEvaluator($params, $this->_debug);
        $this->_traverser->addVisitor($pv);
        
        $code = "<?php $condition;";
        
        if ($this->_debug){
            error_log("<pre>Evaluating access conditions:\n");
            error_log($condition. "\n\n".
            "on params: \n" .
            print_r($params, true) . "\n" .
            "</pre>");
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
                error_log("<pre>\"$expr\" evaluates to " . ($result == true ? "true" : "false") . "</pre>");
            }
            
            return $result;
        } catch (\PhpParser\Error $e) {
            if ($this->_debug){
                error_log("Error parsing access condition \"$condition\": \n" . $e->getMessage());
            }
            return false;   // Access fails if the access condition can't be parsed.
        } catch (\UserFrosting\AuthorizationException $e) {
            if ($this->_debug){
                error_log("Error parsing access condition \"$condition\": \n" . $e->getMessage());
            }
            return false;
        }
    }

}
