<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use RuntimeException;

final class InitialUserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->seedRealUsers();

        if (! app()->isProduction()) {
            $this->seedDummyUsers();
        }
    }

    private function seedRealUsers(): void
    {
        $this->updateOrCreateUser(
            config: $this->requiredSeedUserConfig('superadmin'),
            role: UserRole::Superadmin,
        );

        $this->updateOrCreateUser(
            config: $this->requiredSeedUserConfig('admin'),
            role: UserRole::Admin,
        );
    }

    private function seedDummyUsers(): void
    {
        $password = $this->requiredConfigString('vetpedia.seed_users.dummy_password');

        foreach (range(1, 2) as $number) {
            $this->updateOrCreateUser([
                'name' => sprintf('[Dummy Admin %02d]', $number),
                'email' => sprintf('dummy.admin.%02d@example.test', $number),
                'password' => $password,
            ], UserRole::Admin);
        }

        foreach (range(1, 10) as $number) {
            $this->updateOrCreateUser([
                'name' => sprintf('[Dummy User %02d]', $number),
                'email' => sprintf('dummy.user.%02d@example.test', $number),
                'password' => $password,
            ], UserRole::User);
        }
    }

    /**
     * @param  array{name: string, email: string, password: string}  $config
     */
    private function updateOrCreateUser(array $config, UserRole $role): void
    {
        User::query()->updateOrCreate(
            ['email' => $config['email']],
            [
                'name' => $config['name'],
                'password' => $config['password'],
                'role' => $role,
                'email_verified_at' => now(),
                'suspended_at' => null,
            ],
        );
    }

    /**
     * @return array{name: string, email: string, password: string}
     */
    private function requiredSeedUserConfig(string $key): array
    {
        $config = config('vetpedia.seed_users.'.$key);

        throw_unless(is_array($config), RuntimeException::class, sprintf('Missing seed user configuration [%s].', $key));

        /** @var array<string, mixed> $config */
        return [
            'name' => $this->requiredArrayString($config, 'name', $key),
            'email' => $this->requiredArrayString($config, 'email', $key),
            'password' => $this->requiredArrayString($config, 'password', $key),
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function requiredArrayString(array $config, string $field, string $key): string
    {
        $value = Arr::get($config, $field);

        throw_if(! is_string($value) || $value === '', RuntimeException::class, sprintf('Missing seed user configuration [%s.%s].', $key, $field));

        return $value;
    }

    private function requiredConfigString(string $key): string
    {
        $value = config($key);

        throw_if(! is_string($value) || $value === '', RuntimeException::class, sprintf('Missing configuration [%s].', $key));

        return $value;
    }
}
