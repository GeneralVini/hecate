<?php

declare(strict_types=1);

namespace App\Web\Location\Index;

use App\Organization\Infrastructure\LocationStore;
use App\Organization\Query\LocationListQuery;
use DomainException;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\RequestProvider\RequestProviderInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private LocationListQuery $locations,
        private LocationStore $store,
        private RequestProviderInterface $requestProvider,
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
                        $this->requiredText($data, 'code', 32, 'Código'),
                        $this->requiredText($data, 'name', 160, 'Nome'),
                        $this->optionalText($data, 'description', 255, 'Descrição'),
                    );
                } elseif ($operation === 'update') {
                    $this->store->update(
                        $this->positiveInt($data, 'id'),
                        $this->requiredText($data, 'code', 32, 'Código'),
                        $this->requiredText($data, 'name', 160, 'Nome'),
                        $this->optionalText($data, 'description', 255, 'Descrição'),
                        ($data['active'] ?? null) === '1',
                    );
                } elseif ($operation === 'delete') {
                    $this->store->softDelete($this->positiveInt($data, 'id'));
                } else {
                    throw new DomainException('Operação inválida.');
                }
            } catch (DomainException $e) {
                $errors['form'] = $e->getMessage();
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/template', [
            'locations' => $this->locations->all(),
            'errors' => $errors,
        ]);
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
        return (int) $id;
    }
}
