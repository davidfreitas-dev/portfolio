<?php

declare(strict_types=1);

use App\Domain\Model\Experience;
use App\Domain\Model\Project;
use App\Domain\Model\Technology;
use App\Infrastructure\Http\Middleware\RoleMiddleware;
use App\Presentation\Action\Auth\LoginAction;
use App\Presentation\Action\Auth\LogoutAction;
use App\Presentation\Action\Auth\RequestLoginOtpAction;
use App\Presentation\Action\Auth\RequestPasswordResetAction;
use App\Presentation\Action\Auth\ResetPasswordAction;
use App\Presentation\Action\Auth\ValidateResetCodeAction;
use App\Presentation\Action\Experience\CreateExperienceAction;
use App\Presentation\Action\Experience\DeleteExperienceAction;
use App\Presentation\Action\Experience\GetExperienceAction;
use App\Presentation\Action\Experience\ListExperiencesAction;
use App\Presentation\Action\Experience\UpdateExperienceAction;
use App\Presentation\Action\Project\CreateProjectAction;
use App\Presentation\Action\Project\DeleteProjectAction;
use App\Presentation\Action\Project\GetProjectAction;
use App\Presentation\Action\Project\ListProjectsAction;
use App\Presentation\Action\Project\UpdateProjectAction;
use App\Presentation\Action\Technology\CreateTechnologyAction;
use App\Presentation\Action\Technology\DeleteTechnologyAction;
use App\Presentation\Action\Technology\GetTechnologyAction;
use App\Presentation\Action\Technology\ListTechnologiesAction;
use App\Presentation\Action\Technology\UpdateTechnologyAction;
use App\Presentation\Action\User\ChangePasswordAction;
use App\Presentation\Action\User\DeleteMeAction;
use App\Presentation\Action\User\GetMeAction;
use App\Presentation\Action\User\UpdateMeAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {
    /**
     * Base Routes
     */
    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode([
            'message' => 'Welcome to the Personal Portfolio Site API!',
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/health', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'API is healthy',
            'timestamp' => date('Y-m-d H:i:s'),
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    /**
     * Authentication Routes
     */
    $app->group('/auth', function (RouteCollectorProxy $group): void {
        $group->post('/request-login', RequestLoginOtpAction::class);
        $group->post('/login', LoginAction::class);
        $group->post('/logout', LogoutAction::class);
        $group->post('/forgot', RequestPasswordResetAction::class);
        $group->post('/validate-reset-code', ValidateResetCodeAction::class);
        $group->post('/reset', ResetPasswordAction::class);
    });

    /**
     * User Routes
     */
    $app->group('/users/me', function (RouteCollectorProxy $group): void {
        $group->get('', GetMeAction::class);
        $group->put('', UpdateMeAction::class);
        $group->patch('/change-password', ChangePasswordAction::class);
        $group->delete('', DeleteMeAction::class);
    });

    /**
     * Experience Routes
     */
    $app->group('/experiences', function (RouteCollectorProxy $group): void {
        $group->get('', ListExperiencesAction::class);
        $group->get('/{id}', GetExperienceAction::class);
        $group->post('', CreateExperienceAction::class)->add(new RoleMiddleware('admin'));
        $group->put('/{id}', UpdateExperienceAction::class)->add(new RoleMiddleware('admin'));
        $group->delete('/{id}', DeleteExperienceAction::class)->add(new RoleMiddleware('admin'));
    })->add(new RoleMiddleware(['public', 'user', 'editor', 'admin']));

    /**
     * Project Routes
     */
    $app->group('/projects', function (RouteCollectorProxy $group): void {
        $group->get('', ListProjectsAction::class);
        $group->get('/{id}', GetProjectAction::class);
        $group->post('', CreateProjectAction::class);
        $group->put('/{id}', UpdateProjectAction::class);
        $group->delete('/{id}', DeleteProjectAction::class);
    })->add(new RoleMiddleware(['public', 'user', 'editor', 'admin']));


    /**
     * Technology Routes
     */
    $app->group('/technologies', function (RouteCollectorProxy $group): void {
        $group->get('', ListTechnologiesAction::class);
        $group->get('/{id}', GetTechnologyAction::class);
        $group->post('', CreateTechnologyAction::class)->add(new RoleMiddleware('admin'));
        $group->put('/{id}', UpdateTechnologyAction::class)->add(new RoleMiddleware('admin'));
        $group->delete('/{id}', DeleteTechnologyAction::class)->add(new RoleMiddleware('admin'));
    })->add(new RoleMiddleware(['public', 'user', 'editor', 'admin']));

    /**
     * Image Routes
     */
    $app->get('/images/{folder}/{image}', function (Request $request, Response $response, array $args) {
        $imageDirectoryPath = __DIR__ . '/../storage/';
        $defaultImage = 'no-image.png';
        $imagePath = $imageDirectoryPath . $args['folder'] . '/' . $args['image'];

        if (!file_exists($imagePath)) {
            $imagePath = $imageDirectoryPath . $defaultImage;
        }

        $image = file_get_contents($imagePath);
        $response->getBody()->write($image);
        return $response->withHeader('Content-Type', 'image/jpeg');
    });
};
