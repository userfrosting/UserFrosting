<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Authorize;

use PhpParser\Lexer\Emulative as EmulativeLexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\Parser as Parser;
use PhpParser\PrettyPrinter\Standard as StandardPrettyPrinter;
use PhpParser\Error as PhpParserException;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Account\Model\User;

/**
 * AccessConditionExpression class
 *
 * This class models the evaluation of an authorization condition expression, as associated with permissions.
 * A condition is built as a boolean expression composed of AccessCondition method calls.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see http://www.userfrosting.com/components/#authorization
 */
class AccessConditionExpression
{
    /**
     * @var User A user object, which for convenience can be referenced as 'self' in access conditions.
     */
    protected $user;
    /**
     * @var \PhpParser\Parser The PhpParser object to use (initialized in the ctor)
     */
    protected $parser;
    /**
     * @var NodeTraverser The NodeTraverser object to use (initialized in the ctor)
     */    
    protected $traverser;
    /**
     * @var StandardPrettyPrinter The PrettyPrinter object to use (initialized in the ctor)
     */ 
    protected $prettyPrinter;
    /**
     * @var bool Set to true if you want debugging information printed to the error log.
     */ 
    protected $debug;

    /**
     * Create a new AccessConditionExpression object.
     *
     * @param User A user object, which for convenience can be referenced as 'self' in access conditions.
     * @param bool $debug Set to true if you want debugging information printed to the error log.
     */    
    public function __construct($user, $debug = false)
    {
        $this->user          = $user;
        $this->parser        = new Parser(new EmulativeLexer);
        $this->traverser     = new NodeTraverser;
        $this->prettyPrinter = new StandardPrettyPrinter;
        $this->debug = $debug;
    }
 
    /**
     * Evaluates a condition expression, based on the given parameters.
     *
     * There are two special parameters, `self` and `route`, which are arrays of the current user's data and the current route data, respectively.
     * These get included automatically, and do not need to be passed in.
     * @param string $condition a boolean expression composed of calls to AccessCondition functions.
     * @param array[mixed] $params the parameters to be used when evaluating the expression.
     * @return bool true if the condition is passed for the given parameters, otherwise returns false.
     */      
    public function evaluateCondition($condition, $params)
    {
        // Set the reserved `self` parameters.
        // This replaces any values of `self` specified in the arguments, thus preventing them from being overridden in malicious user input.
        // (For example, from an unfiltered request body).
        $params['self'] = $this->user->export();
        
        // Traverse the parse tree, and execute all function calls as methods of class AccessCondition.
        // Replace the function node with the return value of the method.
        $pv = new ParserNodeFunctionEvaluator($params, $this->debug);
        $this->traverser->addVisitor($pv);
        
        $code = "<?php $condition;";
        
        if ($this->debug) {
            error_log("<pre>Evaluating access conditions:\n");
            error_log($condition. "\n\n".
            "on params: \n" .
            print_r($params, true) . "\n" .
            "</pre>");
        }
        
        try {
            // parse
            $stmts = $this->parser->parse($code);
            
            // traverse
            $stmts = $this->traverser->traverse($stmts);
            
            // Evaluate boolean statement.  It is safe to use eval() here, because our expression has been reduced entirely to a boolean expression.
            $expr = $this->prettyPrinter->prettyPrintExpr($stmts[0]);
            $expr_eval = "return " . $expr . ";\n";
            $result = eval($expr_eval);
            
            if ($this->debug) {
                error_log("<pre>\"$expr\" evaluates to " . ($result == true ? "true" : "false") . "</pre>");
            }
            
            return $result;
        } catch (PhpParserException $e) {
            if ($this->debug) {
                error_log("Error parsing access condition \"$condition\": \n" . $e->getMessage());
            }
            return false;   // Access fails if the access condition can't be parsed.
        } catch (AuthorizationException $e) {
            if ($this->debug) {
                error_log("Error parsing access condition \"$condition\": \n" . $e->getMessage());
            }
            return false;
        }
    }
}
