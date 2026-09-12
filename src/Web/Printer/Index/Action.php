<?php

declare(strict_types=1);

namespace App\Web\Printer\Index;

use App\Organization\Query\LocationListQuery;
use App\Printing\Application\RegisterPrinter;
use App\Printing\Application\RegisterPrinterInput;
use App\Printing\Infrastructure\PrinterWriter;
use App\Printing\Query\PrinterListQuery;
use DomainException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\RequestProvider\RequestProviderInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    private const PAGE_SIZE = 10;

    public function __construct(
        private WebViewRenderer $viewRenderer,
        private PrinterListQuery $printers,
        private LocationListQuery $locations,
        private RegisterPrinter $registerPrinter,
        private PrinterWriter $writer,
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
                    $this->registerPrinter->execute(new RegisterPrinterInput(
                        $this->requiredText($data, 'name', 160, 'Nome'),
                        $this->requiredText($data, 'host', 160, 'Host'),
                        $this->nullablePositiveInt($data, 'location_id'),
                    ));

                    return $this->redirectWithFlash($request->getUri()->getPath(), 'created');
                }

                if ($operation === 'update') {
                    $this->writer->update(
                        $this->positiveInt($data, 'id'),
                        $this->requiredText($data, 'name', 160, 'Nome'),
                        $this->requiredText($data, 'host', 160, 'Host'),
                        $this->nullablePositiveInt($data, 'location_id'),
                        ($data['active'] ?? null) === '1',
                    );

                    return $this->redirectWithFlash($request->getUri()->getPath(), 'updated');
                }

                if ($operation === 'delete') {
                    $this->writer->softDelete($this->positiveInt($data, 'id'));

                    return $this->redirectWithFlash($request->getUri()->getPath(), 'deleted');
                }

                throw new DomainException('Operação inválida.');
            } catch (InvalidArgumentException | DomainException $e) {
                $errors['form'] = $e->getMessage();
            }
        }

        $query = $request->getQueryParams();
        $filters = $this->filters($query);
        $sort = $this->sort($query['sort'] ?? null);
        $direction = $this->direction($query['direction'] ?? null);
        $total = $this->printers->count($filters);
        $pageCount = max(1, (int) ceil($total / self::PAGE_SIZE));
        $page = min($this->pageNumber($query['page'] ?? null), $pageCount);
        $offset = ($page - 1) * self::PAGE_SIZE;

        return $this->viewRenderer->render(__DIR__ . '/template', [
            'printers' => $this->printers->page($filters, $sort, $direction, self::PAGE_SIZE, $offset),
            'locations' => $this->locations->activeOptions(),
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
            'created' => ['type' => 'success', 'message' => 'Impressora cadastrada.'],
            'updated' => ['type' => 'success', 'message' => 'Impressora atualizada.'],
            'deleted' => ['type' => 'success', 'message' => 'Impressora removida.'],
            default => null,
        };
    }

    /**
     * @param array<array-key,mixed> $query
     * @return array{name: string, host: string, location: string, active: string}
     */
    private function filters(array $query): array
    {
        $active = $this->queryString($query, 'active');

        return [
            'name' => $this->queryString($query, 'name'),
            'host' => $this->queryString($query, 'host'),
            'location' => $this->queryString($query, 'location'),
            'active' => in_array($active, ['0', '1'], true) ? $active : '',
        ];
    }

    /** @param array<array-key,mixed> $query */
    private function queryString(array $query, string $field): string
    {
        $value = $query[$field] ?? '';
        return is_string($value) ? trim($value) : '';
    }

    private function sort(mixed $value): string
    {
        if (!is_string($value)) {
            return 'name';
        }

        return in_array($value, ['name', 'host', 'location', 'active', 'last_seen_at'], true) ? $value : 'name';
    }

    private function direction(mixed $value): string
    {
        return is_string($value) && strtolower($value) === 'desc' ? 'desc' : 'asc';
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
    private function positiveInt(array $data, string $field): int
    {
        $value = $data[$field] ?? null;
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new DomainException('Identificador inválido.');
        }
        return $id;
    }

    /** @param array<array-key,mixed> $data */
    private function nullablePositiveInt(array $data, string $field): ?int
    {
        $value = $this->stringValue($data, $field);
        if ($value === '') {
            return null;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new DomainException('Local inválido.');
        }
        return $id;
    }
}
