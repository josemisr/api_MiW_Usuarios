<?php


namespace App\Controller;


use App\Entity\Message;
use App\Entity\Result;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class ApiResultsController
 *
 * @package App\Controller
 *
 * @Route(
 *     path=ApiResultsController::RUTA_API,
 *     name="api_results_"
 * )
 */
class ApiResultsController extends AbstractController
{
    public const RUTA_API = '/api/v1/results';
    private $entityManager;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }
    /**
     * Summary: Returns all results
     * Notes: Returns all results from the system that the result has access to.
     *
     * @param   Request $request
     * @return  Response
     * @Route(
     *     ".{_format}",
     *     defaults={"_format": null},
     *     requirements={
     *         "_format": "json|xml"
     *     },
     *     methods={ Request::METHOD_GET },
     *     name="cget"
     * )
     *
     * @Security(
     *     expression="is_granted('IS_AUTHENTICATED_FULLY')",
     *     statusCode=401,
     *     message="Invalid credentials."
     * )
     */
    public function cgetAction(Request $request): Response
    {
        $results = $this->entityManager
            ->getRepository(Result::class)
            ->findAll();
        $format = Utils::getFormat($request);

        if (empty($results)) {
            $message = new Message(Response::HTTP_NOT_FOUND, Response::$statusTexts[404]);
            return Utils::apiResponse(
                $message->getCode(),
                [ 'message' => $message ],
                $format
            );
        }

        return Utils::apiResponse(
            Response::HTTP_OK,
            [ 'results' => $results ],
            $format
        );
    }

    /**
     * Summary: Returns a result based on a single ID
     * Notes: Returns the result identified by &#x60;resultId&#x60;.
     *
     * @param Request $request
     * @param  int userId user id
     * @return Response
     * @Route(
     *     "/user/{userId}.{_format}",
     *     defaults={ "_format": null },
     *     requirements={
     *          "userId": "\d+",
     *          "_format": "json|xml"
     *     },
     *     methods={ Request::METHOD_GET },
     *     name="getByUser"
     * )
     *
     * @Security(
     *     expression="is_granted('IS_AUTHENTICATED_FULLY')",
     *     statusCode=401,
     *     message="Invalid credentials."
     * )
     */
    public function cgetActionByUser(Request $request,int $userId): Response
    {
        $format = Utils::getFormat($request);
        $user_exist = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy([ 'id' => $userId ]);

        if (null === $user_exist) {    // 400 - Bad Request
            $message = new Message(Response::HTTP_BAD_REQUEST, Response::$statusTexts[400]);
            return Utils::apiResponse(
                $message->getCode(),
                [ 'message' => $message ],
                $format
            );
        }
            $results = $this->entityManager
            ->getRepository(Result::class)
            ->findBy([ 'user' => $user_exist]);
        $format = Utils::getFormat($request);

        if (empty($results)) {
            $message = new Message(Response::HTTP_NOT_FOUND, Response::$statusTexts[404]);
            return Utils::apiResponse(
                $message->getCode(),
                [ 'message' => $message ],
                $format
            );
        }

        return Utils::apiResponse(
            Response::HTTP_OK,
            [ 'results' => $results ],
            $format
        );
    }
    /**
     * Summary: Returns a result based on a single ID
     * Notes: Returns the result identified by &#x60;resultId&#x60;.
     *
     * @param Request $request
     * @param  int $resultId User id
     * @return Response
     * @Route(
     *     "/{resultId}.{_format}",
     *     defaults={ "_format": null },
     *     requirements={
     *          "resultId": "\d+",
     *          "_format": "json|xml"
     *     },
     *     methods={ Request::METHOD_GET },
     *     name="get"
     * )
     *
     * @Security(
     *     expression="is_granted('IS_AUTHENTICATED_FULLY')",
     *     statusCode=401,
     *     message="Invalid credentials."
     * )
     */
    public function getAction(Request $request, int $resultId): Response
    {
        $result = $this->entityManager
            ->getRepository(Result::class)
            ->find($resultId);
        $format = Utils::getFormat($request);

        if (empty($result)) {
            $message = new Message(Response::HTTP_NOT_FOUND, Response::$statusTexts[404]);
            return Utils::apiResponse(
                $message->getCode(),
                [ 'message' => $message ],
                $format
            );
        }

        return Utils::apiResponse(
            Response::HTTP_OK,
            [ 'result' => $result ],
            $format
        );
    }
    /**
     * Summary: Provides the list of HTTP supported methods
     * Notes: Return a &#x60;Allow&#x60; header with a list of HTTP supported methods.
     *
     * @param  int $userId User id
     * @return Response
     * @Route(
     *     "/user/{userId}.{_format}",
     *     defaults={"userId" = 0, "_format": "json"},
     *     requirements={
     *          "userId": "\d+",
     *         "_format": "json|xml"
     *     },
     *     methods={ Request::METHOD_OPTIONS },
     *     name="options_by_user"
     * )
     */
    public function optionsActionByUser(int $userId): Response
    {
        $methods = $userId
            ? [ Request::METHOD_GET,Request::METHOD_DELETE]
            : [ ];

        return new JsonResponse(
            null,
            Response::HTTP_OK,
            [ 'Allow' => implode(', ', $methods) ]
        );
    }

    /**
     * Summary: Provides the list of HTTP supported methods
     * Notes: Return a &#x60;Allow&#x60; header with a list of HTTP supported methods.
     *
     * @param  int $resultId User id
     * @return Response
     * @Route(
     *     "/{resultId}.{_format}",
     *     defaults={"resultId" = 0, "_format": "json"},
     *     requirements={
     *          "resultId": "\d+",
     *         "_format": "json|xml"
     *     },
     *     methods={ Request::METHOD_OPTIONS },
     *     name="options"
     * )
     */
    public function optionsAction(int $resultId): Response
    {
        $methods = $resultId
            ? [ Request::METHOD_GET, Request::METHOD_PUT, Request::METHOD_DELETE ]
            : [ Request::METHOD_GET, Request::METHOD_POST ];

        return new JsonResponse(
            null,
            Response::HTTP_OK,
            [ 'Allow' => implode(', ', $methods) ]
        );
    }
    /**
     * Summary: Deletes a result
     * Notes: Deletes the result identified by &#x60;resultId&#x60;.
     *
     * @param   Request $request
     * @param   int $resultId result id
     * @return  Response
     * @Route(
     *     "/{resultId}.{_format}",
     *     defaults={"_format": null},
     *     requirements={
     *          "resultId": "\d+",
     *         "_format": "json|xml"
     *     },
     *     methods={ Request::METHOD_DELETE },
     *     name="delete"
     * )
     *
     * @Security(
     *     expression="is_granted('IS_AUTHENTICATED_FULLY')",
     *     statusCode=401,
     *     message="Invalid credentials."
     * )
     */
    public function deleteAction(Request $request, int $resultId): Response
    {
        // Puede crear un usuario sólo si tiene ROLE_ADMIN
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new HttpException(   // 403
                Response::HTTP_FORBIDDEN,
                "`Forbidden`: you don't have permission to access"
            );
        }
        $format = Utils::getFormat($request);

        /** @var Result $result*/
        $result = $this->entityManager
            ->getRepository(Result::class)
            ->findOneBy([ 'id' => $resultId ]);

        if (null === $result) {   // 404 - Not Found
            $message = new Message(Response::HTTP_NOT_FOUND, Response::$statusTexts[404]);
            return Utils::apiResponse(
                $message->getCode(),
                [ 'message' => $message ],
                $format
            );
        }

        $this->entityManager->remove($result);
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }


    /**
     * Summary: Returns a result based on a single ID
     * Notes: Returns the result identified by &#x60;resultId&#x60;.
     *
     * @param Request $request
     * @param  int userId user id
     * @return Response
     * @Route(
     *     "/user/{userId}.{_format}",
     *     defaults={ "_format": null },
     *     requirements={
     *          "userId": "\d+",
     *          "_format": "json|xml"
     *     },
     *     methods={ Request::METHOD_DELETE },
     *     name="delete_by_user"
     * )
     *
     * @Security(
     *     expression="is_granted('IS_AUTHENTICATED_FULLY')",
     *     statusCode=401,
     *     message="Invalid credentials."
     * )
     */
    public function deleteActionByUserId(Request $request, int $userId): Response
    {

        // Puede crear un usuario sólo si tiene ROLE_ADMIN
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new HttpException(   // 403
                Response::HTTP_FORBIDDEN,
                "`Forbidden`: you don't have permission to access"
            );
        }
        $format = Utils::getFormat($request);

        $user_exist = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy([ 'id' => $userId ]);

        if (null === $user_exist) {    // 400 - Bad Request
            $message = new Message(Response::HTTP_BAD_REQUEST, Response::$statusTexts[400]);
            return Utils::apiResponse(
                $message->getCode(),
                [ 'message' => $message ],
                $format
            );
        }
        $results = $this->entityManager
            ->getRepository(Result::class)
            ->findBy([ 'user' => $user_exist]);
        $format = Utils::getFormat($request);

        if (null === $results) {   // 404 - Not Found
            $message = new Message(Response::HTTP_NOT_FOUND, Response::$statusTexts[404]);
            return Utils::apiResponse(
                $message->getCode(),
                [ 'message' => $message ],
                $format
            );
        }

        foreach ($results as $result ) {
            $this->entityManager->remove($result);
        }
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
    /**
     * POST action
     *
     * @param Request $request request
     * @return Response
     * @Route(
     *     ".{_format}",
     *     defaults={"_format": null},
     *     requirements={
     *         "_format": "json|xml"
     *     },
     *     methods={ Request::METHOD_POST },
     *     name="post"
     * )
     *
     * @Security(
     *     expression="is_granted('IS_AUTHENTICATED_FULLY')",
     *     statusCode=401,
     *     message="Invalid credentials."
     * )
     */
    public function postAction(Request $request): Response
    {
        // Puede crear un usuario sólo si tiene ROLE_ADMIN
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new HttpException(   // 403
                Response::HTTP_FORBIDDEN,
                "`Forbidden`: you don't have permission to access"
            );
        }
        $body = $request->getContent();
        $postData = json_decode($body, true);
        $format = Utils::getFormat($request);

        if (!isset($postData['result'], $postData['userId'])) {
            // 422 - Unprocessable Entity Faltan datos
            $message = new Message(Response::HTTP_UNPROCESSABLE_ENTITY, Response::$statusTexts[422]);
            return Utils::apiResponse(
                $message->getCode(),
                [ 'message' => $message ],
                $format
            );
        }

        // hay datos -> procesarlos
        $user_exist = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy([ 'id' => $postData['userId'] ]);

        if (null === $user_exist) {    // 400 - Bad Request
            $message = new Message(Response::HTTP_BAD_REQUEST, Response::$statusTexts[400]);
            return Utils::apiResponse(
                $message->getCode(),
                [ 'message' => $message ],
                $format
            );
        }

        // 201 - Created
        $result = new Result(
            $postData['result'],
            $user_exist,
            new DateTime($postData['time'])
        );

        $this->entityManager->persist($result);
        $this->entityManager->flush();

        return Utils::apiResponse(
            Response::HTTP_CREATED,
            [ 'result' => $result ],
            $format
        );
    }

    /**
     * Summary: Updates a result
     * Notes: Updates the result identified by &#x60;resultId&#x60;.
     *
     * @param   Request $request request
     * @param   int $resultId Result id
     * @return  Response
     * @Route(
     *     "/{resultId}.{_format}",
     *     defaults={"_format": null},
     *     requirements={
     *          "resultId": "\d+",
     *         "_format": "json|xml"
     *     },
     *     methods={ Request::METHOD_PUT },
     *     name="put"
     * )
     *
     * @Security(
     *     expression="is_granted('IS_AUTHENTICATED_FULLY')",
     *     statusCode=401,
     *     message="Invalid credentials."
     * )
     */
    public function putAction(Request $request, int $resultId): Response
    {
        // Puede editar otro resultado diferente sólo si tiene ROLE_ADMIN
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new HttpException(   // 403
                Response::HTTP_FORBIDDEN,
                "`Forbidden`: you don't have permission to access"
            );
        }
        $body = $request->getContent();
        $postData = json_decode($body, true);
        $format = Utils::getFormat($request);

        $result = $this->entityManager
            ->getRepository(Result::class)
            ->findOneBy([ 'id' => $resultId ]);

        if (null === $result) {    // 404 - Not Found
            $message = new Message(Response::HTTP_NOT_FOUND, Response::$statusTexts[404]);
            return Utils::apiResponse(
                $message->getCode(),
                [ 'message' => $message ],
                $format
            );
        }

        if (isset($postData['userId'])) {
            $user_exist = $this->entityManager
                ->getRepository(User::class)
                ->findOneBy([ 'id' => $postData['userId'] ]);

            if (null === $user_exist) {    // 400 - Bad Request
                $message = new Message(Response::HTTP_BAD_REQUEST, Response::$statusTexts[400]);
                return Utils::apiResponse(
                    $message->getCode(),
                    [ 'message' => $message ],
                    $format
                );
            }
            $result->setUser($user_exist);
        }

        // result
        if (isset($postData['result'])) {
            $result->setResult($postData['result']);
        }

        // time
        $time= new DateTime($postData['time']);
        if (isset($postData['time'])) {
            $result->setTime($time);
        }

        $this->entityManager->flush();

        return Utils::apiResponse(
            209,                        // 209 - Content Returned
            [ 'result' => $result ],
            $format
        );
    }
}
