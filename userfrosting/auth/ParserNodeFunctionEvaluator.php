<?php

use PhpParser\Node;

// Used to parse access control conditions
class ParserNodeFunctionEvaluator extends PhpParser\NodeVisitorAbstract {

    protected $prettyPrinter;
    protected $acReflector;
    protected $params = [];         // The parameters to be used when calling the methods, as an array
    protected $debug;
    
    public function __construct($params = [], $debug = false){
        $this->acReflector = new ReflectionClass('AccessCondition');
        $this->prettyPrinter = new PhpParser\PrettyPrinter\Standard;
        $this->params = $params;
        $this->debug = $debug;
    }

    public function leaveNode(Node $node) {
        // Look for function calls
        if ($node instanceof PhpParser\Node\Expr\FuncCall) {
            $eval = new Node\Scalar\LNumber;
            
            // Get the method name
            $method = $node->name->toString();
            // Get the method arguments
            $argNodes = $node->args;
            $args = [];
            foreach ($argNodes as $arg){
                $arg_string = $this->prettyPrinter->prettyPrintExpr($arg->value);
                // Resolve variables (placeholders and array paths)
                if (($arg->value instanceof PhpParser\Node\Expr\BinaryOp\Concat) || ($arg->value instanceof PhpParser\Node\Expr\ConstFetch)) {
                    $value = $this->resolveParamPath($arg_string);
                // Resolve arrays
                } else if ($arg->value instanceof PhpParser\Node\Expr\Array_) {
                    $value = $this->resolveArray($arg);
                } else {
                    $value = $arg_string;
                }
                $args[] = $value;
            }
            
            if ($this->debug) {
                //echo "<pre>";
                error_log("Evaluating method '$method' on \n");            
                error_log(print_r($args, true));
            }
            
            // Call the specified function with the specified arguments.
            try{
                $method_handler = $this->acReflector->getMethod($method);     
            } catch (Exception $e){
                throw new UserFrosting\AuthorizationException("Authorization failed: Access condition method '$method' does not exist.");
            }         
    
            $result = $method_handler->invokeArgs(null, $args);
            
            if ($this->debug){
                error_log("Result: " . ($result ? "1" : "0"));
                //echo "</pre>";
            }
            
            return new PhpParser\Node\Scalar\LNumber($result ? "1" : "0");
        }
    }
    
    /* Resolve a parameter path (e.g. "user.id", "post", etc) into its value
    */
    private function resolveParamPath($path){
        $pathTokens = explode(".", $path);
        $value = $this->params;
        foreach ($pathTokens as $token){
            $token = trim($token);
            if (is_array($value) && isset($value[$token])){
                $value = $value[$token];
                continue;
            } else if (is_object($value) && isset($value->$token)) {
                $value = $value->$token;
                continue;
            } else {
                throw new UserFrosting\AuthorizationException("Cannot resolve the path \"$path\".  Error at token \"$token\".");
            }
        }
        return $value;
    }
    
    private function resolveArray($arg){
        $arr = [];
        $items = (array) $arg->value->items;
        foreach ($items as $item){
            if ($item->key)
                $arr[$item->key] = $item->value->value;
            else
                $arr[] = $item->value->value;
        }
        return $arr;
    }
    
}
