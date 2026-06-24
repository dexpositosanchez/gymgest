<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\V1;

use App\Application\DTOs\LoginUserDTO;
use App\Application\DTOs\RegisterUserDTO;
use App\Application\UseCases\LoginUserUseCase;
use App\Application\UseCases\LogoutUserUseCase;
use App\Application\UseCases\RegisterUserUseCase;
use App\Application\UseCases\VerifyEmailUseCase;
use App\Application\UseCases\ResendVerificationEmailUseCase;
use App\Application\UseCases\RequestPasswordResetUseCase;
use App\Application\UseCases\ResetPasswordUseCase;
use App\Infrastructure\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\LoginUserRequest;
use App\Infrastructure\Http\Requests\RegisterUserRequest;
use App\Infrastructure\Http\Requests\ResendVerificationEmailRequest;
use App\Infrastructure\Http\Requests\RequestPasswordResetRequest;
use App\Infrastructure\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Info(
 *     title="GymGest API",
 *     version="1.0.0",
 *     description="Gym management system API"
 * )
 * @OA\Server(
 *     url="/api/v1",
 *     description="API V1 Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class AuthController extends Controller
{
    /** @var RegisterUserUseCase */
    private $registerUserUseCase;
    /** @var LoginUserUseCase */
    private $loginUserUseCase;
    /** @var LogoutUserUseCase */
    private $logoutUserUseCase;
    /** @var VerifyEmailUseCase */
    private $verifyEmailUseCase;
    /** @var ResendVerificationEmailUseCase */
    private $resendVerificationEmailUseCase;
    /** @var RequestPasswordResetUseCase */
    private $requestPasswordResetUseCase;
    /** @var ResetPasswordUseCase */
    private $resetPasswordUseCase;

    public function __construct(
        RegisterUserUseCase $registerUserUseCase,
        LoginUserUseCase $loginUserUseCase,
        LogoutUserUseCase $logoutUserUseCase,
        VerifyEmailUseCase $verifyEmailUseCase,
        ResendVerificationEmailUseCase $resendVerificationEmailUseCase,
        RequestPasswordResetUseCase $requestPasswordResetUseCase,
        ResetPasswordUseCase $resetPasswordUseCase
    ) {
        $this->registerUserUseCase = $registerUserUseCase;
        $this->loginUserUseCase = $loginUserUseCase;
        $this->logoutUserUseCase = $logoutUserUseCase;
        $this->verifyEmailUseCase = $verifyEmailUseCase;
        $this->resendVerificationEmailUseCase = $resendVerificationEmailUseCase;
        $this->requestPasswordResetUseCase = $requestPasswordResetUseCase;
        $this->resetPasswordUseCase = $resetPasswordUseCase;
        $this->middleware('jwt.auth', ['except' => ['register', 'login', 'verify', 'resend', 'requestPasswordReset', 'resetPassword']]);
        $this->middleware('throttle:auth', ['only' => ['login', 'register', 'resend', 'requestPasswordReset']]);
    }

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password","user_type","name","last_name","birth_date","gender"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", minLength=8),
     *             @OA\Property(property="user_type", type="string", enum={"trainer","student"}),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="birth_date", type="string", format="date"),
     *             @OA\Property(property="gender", type="string", enum={"male","female","other"}),
     *             @OA\Property(property="gym_goals", type="string", description="Required for students")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(RegisterUserRequest $request): JsonResponse
    {
        try {
            $dto = new RegisterUserDTO(
                $request->email,
                $request->password,
                $request->user_type,
                $request->name,
                $request->last_name,
                $request->birth_date,
                $request->gender,
                $request->gym_goals
            );

            $user = $this->registerUserUseCase->execute($dto);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->getId()->getValue(),
                    'email' => $user->getEmail()->getValue(),
                    'user_type' => $user->getUserType()->getValue(),
                    'name' => $user->getName()->getValue(),
                    'last_name' => $user->getLastName()->getValue()
                ]
            ], 201);

        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Login user",
     *     description="Login endpoint. Note: Only users with user_type='trainer' can login. Students will receive a 403 error.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=403, description="User is not a trainer")
     * )
     */
    public function login(LoginUserRequest $request): JsonResponse
    {
        try {
            $dto = new LoginUserDTO(
                $request->email,
                $request->password
            );

            $result = $this->loginUserUseCase->execute($dto);

            return response()->json($result);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Login failed'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Logout user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(): JsonResponse
    {
        try {
            $this->logoutUserUseCase->execute();

            return response()->json(['message' => 'Successfully logged out']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Logout failed'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     summary="Get authenticated user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User information",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function me(): JsonResponse
    {
        try {
            $user = JWTAuth::user();

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'name' => $user->name,
                    'last_name' => $user->last_name,
                    'birth_date' => $user->birth_date,
                    'gender' => $user->gender,
                    'gym_goals' => $user->gym_goals
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/auth/email/verify/{id}/{hash}",
     *     summary="Verify email address",
     *     tags={"Authentication"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="expires",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="signature",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Invalid or expired verification link"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function verify(Request $request, string $id, string $hash)
    {
        // Validar signed URL
        if (!$request->hasValidSignature()) {
            return redirect(config('app.frontend_url') . '/verification-failed?reason=invalid');
        }

        try {
            $this->verifyEmailUseCase->execute($id, $hash);

            return redirect(config('app.frontend_url') . '/verification-success');

        } catch (\DomainException $e) {
            return redirect(config('app.frontend_url') . '/verification-failed?reason=not_found');
        } catch (\Exception $e) {
            return redirect(config('app.frontend_url') . '/verification-failed?reason=error');
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/email/resend",
     *     summary="Resend email verification",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification email sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function resend(ResendVerificationEmailRequest $request): JsonResponse
    {
        try {
            $this->resendVerificationEmailUseCase->execute($request->email);

            return response()->json(['message' => 'Email de verificación reenviado']);

        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al reenviar email'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/password/email",
     *     summary="Request password reset",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset email sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     *     )
     */
    public function requestPasswordReset(RequestPasswordResetRequest $request): JsonResponse
    {
        try {
            $this->requestPasswordResetUseCase->execute($request->email);

            return response()->json(['message' => 'Te hemos enviado un email con instrucciones para restablecer tu contraseña']);

        } catch (\DomainException $e) {
            // No revelar si el usuario existe por seguridad
            return response()->json(['message' => 'Te hemos enviado un email con instrucciones para restablecer tu contraseña']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar solicitud'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/password/reset",
     *     summary="Reset password",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","token","password","password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="password", type="string", minLength=8),
     *             @OA\Property(property="password_confirmation", type="string", minLength=8)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid or expired token"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->resetPasswordUseCase->execute(
                $request->email,
                $request->token,
                $request->password
            );

            return response()->json(['message' => 'Contraseña restablecida correctamente']);

        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al restablecer contraseña'], 500);
        }
    }
}