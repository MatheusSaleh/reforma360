<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Models\Empresa;
use App\Models\User;
use App\Rules\Cnpj;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/**
 * Registro: cria a empresa e o primeiro usuário dela.
 */
class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        $input['cnpj'] = preg_replace('/\D/', '', $input['cnpj'] ?? '') ?: null;

        $dados = Validator::make($input, [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password'     => $this->passwordRules(),
            'razao_social' => ['required', 'string', 'max:255'],
            'cnpj'         => ['nullable', 'string', new Cnpj, Rule::unique(Empresa::class)],
        ])->validate();

        return DB::transaction(function () use ($dados): User {
            $empresa = Empresa::query()->create([
                'razao_social' => $dados['razao_social'],
                'cnpj'         => $dados['cnpj'],
            ]);

            return $empresa->usuarios()->create([
                'name'     => $dados['name'],
                'email'    => $dados['email'],
                'password' => Hash::make($dados['password']),
            ]);
        });
    }
}
