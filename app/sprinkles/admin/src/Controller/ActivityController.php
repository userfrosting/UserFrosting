<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Admin\Controller;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Sprinkle\Account\Model\Group;
use UserFrosting\Sprinkle\Account\Model\User;
use UserFrosting\Sprinkle\Account\Util\Password;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;
use UserFrosting\Support\Exception\BadRequestException;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Exception\HttpException;

/**
 * Controller class for activity-related requests.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class ActivityController extends SimpleController
{
    /**
     * Returns activity history for a single user.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getUserActivities($request, $response, $args)
    {
        // URI parameters
        $params = $args;

       // Load request schema
        $schema = new RequestSchema("schema://get-user.json");

        // Whitelist and set parameter defaults
        $transformer = new RequestDataTransformer($schema);
        $data = $transformer->transform($params);

        // Validate, and halt on validation errors.  This is a GET request, so we redirect on validation error.
        $validator = new ServerSideValidator($schema, $this->ci->translator);
        if (!$validator->validate($data)) {
            // TODO: encapsulate the communication of error messages from ServerSideValidator to the BadRequestException
            $e = new BadRequestException();
            foreach ($validator->errors() as $idx => $field) {
                foreach($field as $eidx => $error) {
                    $e->addUserMessage($error);
                }
            }
            throw $e;
        }

        $this->ci->db;
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Get the user from the URL
        $user = $classMapper->staticMethod('user', 'where', 'user_name', $data['user_name'])
            ->first();

        // If the user doesn't exist, return 404
        if (!$user) {
            throw new NotFoundException();
        }

        // GET parameters
        $params = $request->getQueryParams();

        $filters = isset($params['filters']) ? $params['filters'] : [];
        $size = isset($params['size']) ? $params['size'] : null;
        $page = isset($params['page']) ? $params['page'] : null;
        $sortField = isset($params['sort_field']) ? $params['sort_field'] : "occurred_at";
        $sortOrder = isset($params['sort_order']) ? $params['sort_order'] : "asc";

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_user_activities', [
            'user' => $user
        ])) {
            throw new ForbiddenException();
        }

        $query = $classMapper->createInstance('activity')->where('user_id', $user->id);

        // Count unpaginated total
        $total = $query->count();

        // Apply filters
        $filtersApplied = false;
        foreach ($filters as $name => $value) {
            $query = $query->like($name, $value);

            $filtersApplied = true;
        }

        $totalFiltered = $query->count();

        $query = $query->orderBy($sortField, $sortOrder);

        // Paginate
        if (($page !== null) && ($size !== null)) {
            $offset = $size*$page;
            $query = $query->skip($offset)->take($size);
        }

        $collection = collect($query->get());

        $result = [
            "count" => $total,
            "rows" => $collection->values()->toArray(),
            "count_filtered" => $totalFiltered
        ];

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }
}
