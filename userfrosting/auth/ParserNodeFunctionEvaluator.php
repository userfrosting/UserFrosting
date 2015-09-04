<?php

use PhpParser\Node;

/**
 * ParserNodeFunctionEvaluator class
 *
 * This class parses access control condition expressions.
 * DO NOT CHANGE!  This is a core UserFrosting file, and should not need to be changed by developers.
 * 
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/components/#authentication
 */
class ParserNodeFunctionEvaluator extends PhpParser\NodeVisitorAbstract {

    /**
     * @var ReflectionClass The Reflection object to use for evaluating AccessCondition methods (initialized in the ctor)
     */ 
    protected $acReflector;
    /**
     * @var \PhpParser\PrettyPrinter\Standard The PrettyPrinter object to use (initialized in the ctor)
     */ 
    protected $prettyPrinter;
    /**
     * @var array The parameters to be used when evaluating the methods in the condition expression, as an array.
     */ 
    protected $params = []; 
    /**
     * @var bool Set to true if you want debugging information printed to the error log.
     */
    protected $debug;
    
    /**
     * Create a new ParserNodeFunctionEvaluator object.
     *
     * @param array $params The parameters to be used when evaluating the methods in the condition expression, as an array.
     * @param bool $debug Set to true if you want debugging information printed to the error log.
     */    
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
    
    /**
     * Resolve a parameter path (e.g. "user.id", "post", etc) into its value.
     *
     * @param string $path the name of the parameter to resolve, based on the parameters set in this object.
     * @throws Exception the path could not be resolved.  Path is malformed or key does not exist.
     * @return mixed the value of the specified parameter.
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
    
    /**
     * Resolve an array expression in a condition expression into an actual array.
     *
     * @param string $arg the array, represented as a string.
     * @return array[mixed] the array, as a plain ol' PHP array.
     */    
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
