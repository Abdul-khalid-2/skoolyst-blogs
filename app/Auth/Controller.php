<?php

/**
 * Handles POST /auth/login, POST /auth/logout, GET /auth/me.
 */
class AuthController
{
    public static function login(Request $request): void
    {
        $validator = new Validator($request->body, [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            Response::error('Validation failed.', 422, $validator->errors());
            return;
        }

        $email = trim((string) $request->input('email'));
        $password = (string) $request->input('password');

        $row = AuthRepository::findByEmail($email);

        // Same message whether the email doesn't exist or the password is
        // wrong — never reveal which one it was.
        if ($row === null || !password_verify($password, $row['password_hash'])) {
            Response::unauthorized('Invalid email or password.');
            return;
        }

        if ($row['status'] !== 'active') {
            Response::forbidden('This account has been suspended.');
            return;
        }

        AuthMiddleware::login($row);

        Response::success(AuthUser::fromRow($row)->toArray());
    }

    public static function logout(Request $request): void
    {
        AuthMiddleware::logout();
        Response::success(['message' => 'Logged out.']);
    }

    public static function me(Request $request): void
    {
        $user = AuthMiddleware::currentUser();

        if ($user === null) {
            Response::unauthorized('Not logged in.');
            return;
        }

        Response::success($user->toArray());
    }
}
