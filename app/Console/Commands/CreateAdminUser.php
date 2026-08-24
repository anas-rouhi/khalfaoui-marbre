<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password as promptPassword;
use function Laravel\Prompts\text;

class CreateAdminUser extends Command
{
    protected $signature = 'marbre:admin
                            {--name= : Nom affiché dans le back-office}
                            {--email= : Adresse e-mail de connexion}
                            {--password= : Mot de passe (demandé si absent)}';

    protected $description = 'Créer (ou promouvoir) un compte administrateur pour le back-office /admin';

    public function handle(): int
    {
        $name = $this->option('name') ?: text(
            label: 'Nom de l\'administrateur',
            placeholder: 'Khalfaoui',
            required: true,
        );

        $email = $this->option('email') ?: text(
            label: 'Adresse e-mail de connexion',
            placeholder: 'contact@khalfaoui-marbre.com',
            required: true,
        );

        $password = $this->option('password') ?: promptPassword(
            label: 'Mot de passe (8 caractères minimum)',
            required: true,
        );

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', Password::min(8)],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        // Un e-mail déjà connu est promu plutôt que dupliqué : la commande peut
        // donc aussi servir à redonner l'accès à un compte existant.
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->newLine();
        $this->components->info(
            $user->wasRecentlyCreated
                ? "Compte administrateur créé : {$user->email}"
                : "Compte existant promu administrateur : {$user->email}"
        );
        $this->components->bulletList([
            'Connexion : '.url('/admin/login'),
        ]);

        return self::SUCCESS;
    }
}
