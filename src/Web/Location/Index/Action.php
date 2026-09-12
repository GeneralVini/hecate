<?php

declare(strict_types=1);

namespace App\Web\Location\Index;

use App\Organization\Infrastructure\LocationStore;
use App\Organization\Query\LocationListQuery;
use DomainException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\RequestProvider\RequestProviderInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    private const PAGE_SIZE = 10;

    public function __construct(
        private WebViewRenderer $viewRenderer,
        private LocationListQuery $locations,
        private LocationStore $store,
        private RequestProviderInterface $requestProvider,
        private ResponseFactoryInterface $responseFactory,
    ) {
    }

    public function __invoke(): ResponseInterface
    {
        $errors = [];
        $request = $this->requestProvider->get();

        if (strtoupper($request->getMethod()) === 'POST') {
            $body = $request->getParsedBody();
            $data = is_array($body) ? $body : [];

            try {
                $operation = $this->stringValue($data, 'operation');
                if ($operation === 'create') {
                    $this->store->create(
                        $this->requiredText($data, 'name', 160, 'Nome'),
                        $this->optionalText($data, 'description', 255, 'Descrição'),
                    );

                    return $this->redirectWithFlash($request->getUri()->getPath(), 'created');
                }

                if ($operation === 'update') {
                    $this->store->update(
                        $this->positiveInt($data, 'id'),
                        $this->requiredText($data, 'name', 160, 'Nome'),
                        $this->optionalText($data, 'description', 255, 'Descrição'),
                        ($data['active'] ?? null) === '1',
                    );

                    return $this->redirectWithFlash($request->getUri()->getPath(), 'updated');
                }

                if ($operation === 'delete') {
                    $this->store->softDelete($this->positiveInt($data, 'id'));

                    return $this->redirectWithFlash($request->getUri()->getPath(), 'deleted');
                }

                throw new DomainException('Operação inválida.');
            } catch (DomainException $e) {
                $errors['form'] = $e->getMessage();
            }
        }

        $query = $request->getQueryParams();
        $filters = $this->filters($query);
        [$sort, $direction] = $this->sorting($query);
        $total = $this->locations->count($filters);
        $pageCount = max(1, (int) ceil($total / self::PAGE_SIZE));
        $page = min($this->pageNumber($query['page'] ?? null), $pageCount);
        $offset = ($page - 1) * self::PAGE_SIZE;

        return $this->viewRenderer->render(__DIR__ . '/template', [
            'locations' => $this->locations->page($filters, self::PAGE_SIZE, $offset, $sort, $direction),
            'errors' => $errors,
            'flash' => $this->flashMessage($query['flash'] ?? null),
            'filters' => $filters,
            'sort' => $sort,
            'direction' => $direction,
            'page' => $page,
            'pageCount' => $pageCount,
            'pageSize' => self::PAGE_SIZE,
            'total' => $total,
        ]);
    }

    private function redirectWithFlash(string $path, string $flash): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(303)
            ->withHeader('Location', $path . '?flash=' . rawurlencode($flash));
    }

    /** @return array{type: string, message: string}|null */
    private function flashMessage(mixed $value): ?array
    {
        if (!is_string($value)) {
            return null;
        }

        return match ($value) {
            'created' => ['type' => 'success', 'message' => 'Local cadastrado.'],
            'updated' => ['type' => 'success', 'message' => 'Local atualizado.'],
            'deleted' => ['type' => 'success', 'message' => 'Local removido.'],
            default => null,
        };
    }

    /**
     * @param array<array-key,mixed> $query
     * @return array{code: string, name: string, description: string, active: string}
     */
    private function filters(array $query): array
    {
        $active = $this->queryString($query, 'active');

        return [
            'code' => $this->queryString($query, 'code'),
            'name' => $this->queryString($query, 'name'),
            'description' => $this->queryString($query, 'description'),
            'active' => in_array($active, ['0', '1'], true) ? $active : '',
        ];
    }

    /**
     * @param array<array-key,mixed> $query
     * @return array{0: string, 1: string}
     */
    private function sorting(array $query): array
    {
        $sort = $this->queryString($query, 'sort');
        $direction = strtolower($this->queryString($query, 'direction'));

        if (!in_array($sort, ['code', 'name', 'description', 'active'], true)) {
            $sort = 'name';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        return [$sort, $direction];
    }

    /** @param array<array-key,mixed> $query */
    private function queryString(array $query, string $field): string
    {
        $value = $query[$field] ?? '';
        return is_string($value) ? trim($value) : '';
    }

    private function pageNumber(mixed $value): int
    {
        $page = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $page === false ? 1 : $page;
    }

    /** @param array<array-key,mixed> $data */
    private function stringValue(array $data, string $field): string
    {
        $value = $data[$field] ?? '';
        return is_string($value) ? trim($value) : '';
    }

    /** @param array<array-key,mixed> $data */
    private function requiredText(array $data, string $field, int $maxLength, string $label): string
    {
        $value = $this->stringValue($data, $field);
        if ($value === '' || mb_strlen($value) > $maxLength) {
            throw new DomainException(sprintf('%s deve conter entre 1 e %d caracteres.', $label, $maxLength));
        }
        return $value;
    }

    /** @param array<array-key,mixed> $data */
    private function optionalText(array $data, string $field, int $maxLength, string $label): ?string
    {
        $value = $this->stringValue($data, $field);
        if (mb_strlen($value) > $maxLength) {
            throw new DomainException(sprintf('%s deve conter até %d caracteres.', $label, $maxLength));
        }
        return $value === '' ? null : $value;
    }

    /** @param array<array-key,mixed> $data */
    private function positiveInt(array $data, string $field): int
    {
        $value = $data[$field] ?? null;
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new DomainException('Identificador inválido.');
        }
        return $id;
    }
}
