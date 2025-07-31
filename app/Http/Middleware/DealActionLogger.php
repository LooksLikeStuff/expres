<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\DealLogHelper;
use Illuminate\Support\Facades\Log;

class DealActionLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Логируем только действия, связанные со сделками
        if ($this->shouldLogRequest($request)) {
            $this->logBefore($request);
        }

        $response = $next($request);

        // Логируем после выполнения запроса, если нужно
        if ($this->shouldLogRequest($request)) {
            $this->logAfter($request, $response);
        }

        return $response;
    }

    /**
     * Определяем, нужно ли логировать этот запрос
     */
    private function shouldLogRequest(Request $request): bool
    {
        $route = $request->route();
        if (!$route) {
            return false;
        }

        $routeName = $route->getName();
        $uri = $request->getRequestUri();

        // Логируем запросы, связанные со сделками
        $dealRoutes = [
            'deals.store',
            'deal.update',
            'deals.destroy',
            'deal.change_logs',
            'deal.global_logs',
        ];

        // Или URI содержит паттерны сделок
        $dealPatterns = [
            '/deal/',
            '/deals/',
        ];

        if (in_array($routeName, $dealRoutes)) {
            return true;
        }

        foreach ($dealPatterns as $pattern) {
            if (strpos($uri, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Логирование перед выполнением запроса
     */
    private function logBefore(Request $request): void
    {
        try {
            $route = $request->route();
            $routeName = $route ? $route->getName() : 'unknown';
            $method = $request->getMethod();
            $uri = $request->getRequestUri();

            Log::info("Deal action request started", [
                'route' => $routeName,
                'method' => $method,
                'uri' => $uri,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

        } catch (\Exception $e) {
            Log::error("Error in DealActionLogger before: " . $e->getMessage());
        }
    }

    /**
     * Логирование после выполнения запроса
     */
    private function logAfter(Request $request, Response $response): void
    {
        try {
            $route = $request->route();
            $routeName = $route ? $route->getName() : 'unknown';
            $statusCode = $response->getStatusCode();

            Log::info("Deal action request completed", [
                'route' => $routeName,
                'status_code' => $statusCode,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);

            // Если это успешное действие со сделкой, создаем дополнительный лог
            if ($statusCode >= 200 && $statusCode < 300) {
                $this->createActionLog($request, $response);
            }

        } catch (\Exception $e) {
            Log::error("Error in DealActionLogger after: " . $e->getMessage());
        }
    }

    /**
     * Создание специального лога действия
     */
    private function createActionLog(Request $request, Response $response): void
    {
        try {
            $route = $request->route();
            $routeName = $route ? $route->getName() : null;
            $dealId = $this->extractDealId($request);

            if (!$dealId) {
                return;
            }

            $action = $this->determineAction($request, $routeName);
            if (!$action) {
                return;
            }

            // Создаем лог через Helper
            DealLogHelper::logCustomAction(
                $dealId,
                $action,
                [
                    'route' => $routeName,
                    'method' => $request->getMethod(),
                    'status_code' => $response->getStatusCode(),
                ],
                'system'
            );

        } catch (\Exception $e) {
            Log::error("Error creating action log: " . $e->getMessage());
        }
    }

    /**
     * Извлекаем ID сделки из запроса
     */
    private function extractDealId(Request $request): ?int
    {
        // Из параметров маршрута
        $dealId = $request->route('id') ?? $request->route('deal');
        if ($dealId) {
            return (int) $dealId;
        }

        // Из данных формы
        $dealId = $request->input('deal_id') ?? $request->input('id');
        if ($dealId) {
            return (int) $dealId;
        }

        // Из URL паттернов
        if (preg_match('/\/deal\/(\d+)/', $request->getRequestUri(), $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Определяем тип действия по запросу
     */
    private function determineAction(Request $request, ?string $routeName): ?string
    {
        $method = $request->getMethod();
        
        switch ($method) {
            case 'POST':
                if (strpos($routeName, 'store') !== false) {
                    return 'Просмотрена страница создания сделки';
                }
                return 'Выполнено POST действие со сделкой';
            
            case 'PUT':
            case 'PATCH':
                return 'Просмотрена страница редактирования сделки';
            
            case 'DELETE':
                return 'Просмотрена страница удаления сделки';
            
            case 'GET':
                if (strpos($routeName, 'logs') !== false) {
                    return 'Просмотрены логи сделки';
                }
                if (strpos($routeName, 'edit') !== false) {
                    return 'Открыта форма редактирования сделки';
                }
                return 'Просмотрена информация о сделке';
            
            default:
                return null;
        }
    }
}
