<?php

namespace App\Controllers\Admin;

use App\Controllers\Traits\FileModelController;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Vesp\Controllers\ModelController;

class Users extends ModelController
{
    use FileModelController;

    protected string|array $scope = 'users';
    protected string $model = User::class;
    public array $attachments = ['avatar'];
    public array $allowedTypes = ['avatar' => 'image/'];

    protected function beforeGet(Builder $c): Builder
    {
        return $c->with('avatar:id,uuid,updated_at');
    }

    protected function beforeCount(Builder $c): Builder
    {
        if ($query = trim($this->getProperty('query', ''))) {
            $c->where(
                static function (Builder $c) use ($query) {
                    $c->where('username', 'LIKE', "%$query%");
                    $c->orWhere('fullname', 'LIKE', "%$query%");
                    $c->orWhere('email', 'LIKE', "%$query%");
                    $c->orWhereHas('tokens', static function (Builder $c) use ($query) {
                        $c->where('ip', 'LIKE', "$query%");
                    });
                }
            );
        }
        if ($roleId = $this->getProperty('role_id')) {
            $c->where('role_id', $roleId);
        }

        return $c;
    }

    protected function beforeSave(Model $record): ?ResponseInterface
    {
        try {
            /** @var User $record */
            $record->fillData($this->getProperties());
        } catch (Throwable $e) {
            return $this->failure($e->getMessage());
        }

        if ($error = $this->processFiles($record)) {
            return $error;
        }

        if ($record->blocked) {
            $record->tokens()->update(['active' => false]);
        }

        if ($record->readonly_until?->timestamp <= time()) {
            $record->readonly_until = null;
        }
        if (!$record->readonly_reason) {
            $record->readonly_reason = null;
        }

        return null;
    }

    protected function afterCount(Builder $c): Builder
    {
        $c->with('role:id,title,color');
        $c->with('avatar:id,uuid,updated_at');
        $c->with('currentSubscription:id,user_id,level_id', 'currentSubscription.level:id,title,color');

        return $c;
    }

    protected function beforeDelete(Model $record): ?ResponseInterface
    {
        /** @var User $record */
        if ($this->user->id === $record->id) {
            return $this->failure('errors.user.delete_own');
        }

        return null;
    }

    public function prepareRow(Model $object): array
    {
        /** @var User $object */
        $array = $object->toArray();
        if ($object->readonly_until) {
            $array['readonly_until'] = $object->readonly_until->toDateTimeString();
        }

        return $array;
    }
}