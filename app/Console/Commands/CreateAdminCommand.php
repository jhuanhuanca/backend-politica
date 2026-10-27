<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\IdentityHasher;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create
        {--email= : Correo del administrador}
        {--nick=admin : Nick de acceso}
        {--name=Administrador : Nombre visible}
        {--department=La Paz : Departamento}';

    protected $description = 'Crea el único administrador. La contraseña se pide en pantalla, no se guarda en .env.';

    public function handle(): int
    {
        if (User::query()->where('is_admin', true)->exists()) {
            $this->error('Ya existe un administrador. No se crea otro.');

            return self::FAILURE;
        }

        $email = trim((string) ($this->option('email') ?: $this->ask('Correo del administrador')));
        $nick = trim((string) $this->option('nick'));
        $name = trim((string) $this->option('name'));
        $department = trim((string) $this->option('department'));
        $password = (string) $this->secret('Contraseña (no se guarda en .env)');
        $confirm = (string) $this->secret('Confirmá la contraseña');

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('El correo no es válido.');

            return self::FAILURE;
        }

        if ($nick === '') {
            $this->error('El nick es obligatorio.');

            return self::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->error('La contraseña debe tener al menos 8 caracteres.');

            return self::FAILURE;
        }

        if ($password !== $confirm) {
            $this->error('Las contraseñas no coinciden.');

            return self::FAILURE;
        }

        if (User::query()->where(function ($query) use ($email, $nick): void {
            $query->where('email', $email)->orWhere('nick', $nick);
        })->exists()) {
            $this->error('Ese correo o nick ya está en uso.');

            return self::FAILURE;
        }

        try {
            User::query()->create([
                'name' => $name !== '' ? $name : 'Administrador',
                'nick' => $nick,
                'department' => $department !== '' ? $department : 'La Paz',
                'email' => $email,
                'email_hash' => app(IdentityHasher::class)->hashEmail($email),
                'password' => $password,
                'is_admin' => true,
            ]);
        } catch (ValidationException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Administrador creado: {$nick} <{$email}>");

        return self::SUCCESS;
    }
}
