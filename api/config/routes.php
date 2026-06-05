<?php

declare(strict_types=1);

use App\Infrastructure\Http\Middleware\HttpCacheInvalidationMiddleware;
use App\Infrastructure\Http\Middleware\HttpCacheMiddleware;
use App\Infrastructure\Http\Middleware\RoleMiddleware;
use App\Presentation\Action\Auth\LoginAction;
use App\Presentation\Action\Auth\LogoutAction;
use App\Presentation\Action\Auth\RequestLoginOtpAction;
use App\Presentation\Action\Auth\RequestPasswordResetAction;
use App\Presentation\Action\Auth\ResetPasswordAction;
use App\Presentation\Action\Auth\ValidateResetCodeAction;
use App\Presentation\Action\Experience\Admin\CreateExperienceAction as AdminCreateExperienceAction;
use App\Presentation\Action\Experience\Admin\DeleteExperienceAction as AdminDeleteExperienceAction;
use App\Presentation\Action\Experience\Admin\GetExperienceAction as AdminGetExperienceAction;
use App\Presentation\Action\Experience\Admin\ListExperiencesAction as AdminListExperiencesAction;
use App\Presentation\Action\Experience\Admin\UpdateExperienceAction as AdminUpdateExperienceAction;
use App\Presentation\Action\Experience\GetExperienceAction;
use App\Presentation\Action\Experience\ListExperiencesAction;
use App\Presentation\Action\Health\HealthAction;
use App\Presentation\Action\Image\GetImageAction;
use App\Presentation\Action\Project\Admin\DeleteProjectAction as AdminDeleteProjectAction;
use App\Presentation\Action\Project\Admin\GetProjectAction as AdminGetProjectAction;
use App\Presentation\Action\Project\Admin\ListProjectsAction as AdminListProjectsAction;
use App\Presentation\Action\Project\Admin\SaveProjectAction as AdminSaveProjectAction;
use App\Presentation\Action\Project\GetProjectAction;
use App\Presentation\Action\Project\ListProjectsAction;
use App\Presentation\Action\Technology\Admin\DeleteTechnologyAction as AdminDeleteTechnologyAction;
use App\Presentation\Action\Technology\Admin\GetTechnologyAction as AdminGetTechnologyAction;
use App\Presentation\Action\Technology\Admin\ListTechnologiesAction as AdminListTechnologiesAction;
use App\Presentation\Action\Technology\Admin\SaveTechnologyAction as AdminSaveTechnologyAction;
use App\Presentation\Action\Technology\GetTechnologyAction;
use App\Presentation\Action\Technology\ListTechnologiesAction;
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

    $app->get('/health', HealthAction::class);

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
    })->add(new RoleMiddleware(['user', 'editor', 'admin']));

    /**
     * Public Routes (for the Site)
     */
    $app->group('/public', function (RouteCollectorProxy $group): void {
        $group->get('/experiences', ListExperiencesAction::class);
        $group->get('/experiences/{id}', GetExperienceAction::class);

        $group->get('/projects', ListProjectsAction::class);
        $group->get('/projects/{id}', GetProjectAction::class);

        $group->get('/technologies', ListTechnologiesAction::class);
        $group->get('/technologies/{id}', GetTechnologyAction::class);
    })
    ->add(HttpCacheMiddleware::class)
    ->add(new RoleMiddleware(['public', 'user', 'editor', 'admin']));

    /**
     * Admin Routes (for the CMS)
     */
    $app->group('/admin', function (RouteCollectorProxy $group): void {
        // Experiences
        $group->group('/experiences', function (RouteCollectorProxy $adminGroup): void {
            $adminGroup->get('', AdminListExperiencesAction::class);
            $adminGroup->get('/{id}', AdminGetExperienceAction::class);
            $adminGroup->post('', AdminCreateExperienceAction::class);
            $adminGroup->put('/{id}', AdminUpdateExperienceAction::class);
            $adminGroup->delete('/{id}', AdminDeleteExperienceAction::class);
        });

        // Projects
        $group->group('/projects', function (RouteCollectorProxy $adminGroup): void {
            $adminGroup->get('', AdminListProjectsAction::class);
            $adminGroup->get('/{id}', AdminGetProjectAction::class);
            $adminGroup->post('', AdminSaveProjectAction::class);
            $adminGroup->delete('/{id}', AdminDeleteProjectAction::class);
        });

        // Technologies
        $group->group('/technologies', function (RouteCollectorProxy $adminGroup): void {
            $adminGroup->get('', AdminListTechnologiesAction::class);
            $adminGroup->get('/{id}', AdminGetTechnologyAction::class);
            $adminGroup->post('', AdminSaveTechnologyAction::class);
            $adminGroup->delete('/{id}', AdminDeleteTechnologyAction::class);
        });
    })
    ->add(HttpCacheInvalidationMiddleware::class)
    ->add(new RoleMiddleware(['admin', 'editor']));

    /**
     * Legacy / Compatibility Routes (mapped to Public actions)
     */
    $app->get('/experiences', ListExperiencesAction::class)->add(HttpCacheMiddleware::class);
    $app->get('/experiences/{id}', GetExperienceAction::class)->add(HttpCacheMiddleware::class);
    $app->get('/projects', ListProjectsAction::class)->add(HttpCacheMiddleware::class);
    $app->get('/projects/{id}', GetProjectAction::class)->add(HttpCacheMiddleware::class);
    $app->get('/technologies', ListTechnologiesAction::class)->add(HttpCacheMiddleware::class);
    $app->get('/technologies/{id}', GetTechnologyAction::class)->add(HttpCacheMiddleware::class);

    /**
     * Image Routes
     */
    $app->get('/images/{folder}/{image}', GetImageAction::class);
};
