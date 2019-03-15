<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Authorize;

use Monolog\Logger;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard as StandardPrettyPrinter;

/**
 * ParserNodeFunctionEvaluator class
 *
 * This class parses access control condition expressions.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see http://www.userfrosting.com/components/#authorization
 */
class ParserNodeFunctionEvaluator extends NodeVisitorAbstract
{
    /**
     * @var array[callable] An array of callback functions to be used when evaluating a condition expression.
     */
    protected $callbacks;

    /**
     * @var \PhpParser\PrettyPrinter\Standard The PrettyPrinter object to use (initialized in the ctor)
     */
    protected $prettyPrinter;

    /**
     * @var array The parameters to be used when evaluating the methods in the condition expression, as an array.
     */
    protected $params = [];

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var bool Set to true if you want debugging information printed to the auth log.
     */
    protected $debug;

    /**
     * Create a new ParserNodeFunctionEvaluator object.
     *
     * @param array  $callbacks The parameters to be used when evaluating the methods in the condition expression, as an array.
     * @param Logger $logger    A Monolog logger, used to dump debugging info for authorization evaluations.
     * @param bool   $debug     Set to true if you want debugging information printed to the auth log.
     */
    public function __construct($callbacks, Logger $logger, $debug = false)
    {
        $this->callbacks = $callbacks;
        $this->prettyPrinter = new StandardPrettyPrinter();
        $this->logger = $logger;
        $this->debug = $debug;
        $this->params = [];
    }

    public function leaveNode(Node $node)
    {
        // Look for function calls
        if ($node instanceof \PhpParser\Node\Expr\FuncCall) {
            $eval = new \PhpParser\Node\Scalar\LNumber();

            // Get the method name
            $callbackName = $node->name->toString();
            // Get the method arguments
            $argNodes = $node->args;

            $args = [];
            $argsInfo = [];
            foreach ($argNodes as $arg) {
                $argString = $this->prettyPrinter->prettyPrintExpr($arg->value);

                // Debugger info
                $currentArgInfo = [
                    'expression' => $argString
                ];
                // Resolve parameter placeholders ('variable' names (either single-word or array-dot identifiers))
                if (($arg->value instanceof \PhpParser\Node\Expr\BinaryOp\Concat) || ($arg->value instanceof \PhpParser\Node\Expr\ConstFetch)) {
                    $value = $this->resolveParamPath($argString);
                    $currentArgInfo['type'] = 'parameter';
                    $currentArgInfo['resolved_value'] = $value;
                // Resolve arrays
                } elseif ($arg->value instanceof \PhpParser\Node\Expr\Array_) {
                    $value = $this->resolveArray($arg);
                    $currentArgInfo['type'] = 'array';
                    $currentArgInfo['resolved_value'] = print_r($value, true);
                // Resolve strings
                } elseif ($arg->value instanceof \PhpParser\Node\Scalar\String_) {
                    $value = $arg->value->value;
                    $currentArgInfo['type'] = 'string';
                    $currentArgInfo['resolved_value'] = $value;
                // Resolve numbers
                } elseif ($arg->value instanceof \PhpParser\Node\Scalar\DNumber) {
                    $value = $arg->value->value;
                    $currentArgInfo['type'] = 'float';
                    $currentArgInfo['resolved_value'] = $value;
                } elseif ($arg->value instanceof \PhpParser\Node\Scalar\LNumber) {
                    $value = $arg->value->value;
                    $currentArgInfo['type'] = 'integer';
                    $currentArgInfo['resolved_value'] = $value;
                // Anything else is simply interpreted as its literal string value
                } else {
                    $value = $argString;
                    $currentArgInfo['type'] = 'unknown';
                    $currentArgInfo['resolved_value'] = $value;
                }

                $args[] = $value;
                $argsInfo[] = $currentArgInfo;
            }

            if ($this->debug) {
                if (count($args)) {
                    $this->logger->debug("Evaluating callback '$callbackName' on: ", $argsInfo);
                } else {
                    $this->logger->debug("Evaluating callback '$callbackName'...");
                }
            }

            // Call the specified access condition callback with the specified arguments.
            if (isset($this->callbacks[$callbackName]) && is_callable($this->callbacks[$callbackName])) {
                $result = call_user_func_array($this->callbacks[$callbackName], $args);
            } else {
                throw new AuthorizationException("Authorization failed: Access condition method '$callbackName' does not exist.");
            }

            if ($this->debug) {
                $this->logger->debug('Result: ' . ($result ? '1' : '0'));
            }

            return new \PhpParser\Node\Scalar\LNumber($result ? '1' : '0');
        }
    }

    /**
     * Set params
     *
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Resolve an array expression in a condition expression into an actual array.
     *
     * @param  string       $arg the array, represented as a string.
     * @return array[mixed] the array, as a plain ol' PHP array.
     */
    private function resolveArray($arg)
    {
        $arr = [];
        $items = (array) $arg->value->items;
        foreach ($items as $item) {
            if ($item->key) {
                $arr[$item->key] = $item->value->value;
            } else {
                $arr[] = $item->value->value;
            }
        }

        return $arr;
    }

    /**
     * Resolve a parameter path (e.g. "user.id", "post", etc) into its value.
     *
     * @param  string     $path the name of the parameter to resolve, based on the parameters set in this object.
     * @throws \Exception the path could not be resolved.  Path is malformed or key does not exist.
     * @return mixed      the value of the specified parameter.
     */
    private function resolveParamPath($path)
    {
        $pathTokens = explode('.', $path);
        $value = $this->params;
        foreach ($pathTokens as $token) {
            $token = trim($token);
            if (is_array($value) && isset($value[$token])) {
                $value = $value[$token];
                continue;
            } elseif (is_object($value) && isset($value->$token)) {
                $value = $value->$token;
                continue;
            } else {
                throw new AuthorizationException("Cannot resolve the path \"$path\".  Error at token \"$token\".");
            }
        }

        return $value;
    }
}
