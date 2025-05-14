<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ForgetPasswordController
{
    public function sendResetMail(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        // Check if the email exists in the database
        $user = \App\Models\User::where('email', $validated['email'])->first();

        if (!$user) {
            Log::warning('Password reset attempt for non-existent email: ' . $validated['email']);
            return response()->json(['message' => 'The email address does not exist in our records.'], 404);
        }

        // Initialize PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = env('MAIL_PORT');

            // Recipients
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($validated['email']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = 'Click <a href="' . url('/reset-password?email=' . urlencode($validated['email'])) . '">here</a> to reset your password.';
            $mail->AltBody = 'Click here to reset your password: ' . url('/reset-password?email=' . urlencode($validated['email']));

            // Send the email
            $mail->send();

            Log::info('Password reset email sent successfully to: ' . $validated['email']);
            return response()->json(['message' => 'Reset password email sent successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email to ' . $validated['email'] . '. Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send email.'], 500);
        }
    }

    public function showResetForm(Request $request) {
        return Inertia::render('ForgetPassword', [
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'passwordRepeat' => 'required|min:6',
        ]);

        // Log the password and passwordRepeat for debugging
    Log::debug('Password reset attempt:', [
        'email' => $validated['email'],
        'password' => $validated['password'],
        'passwordRepeat' => $validated['passwordRepeat'],
    ]);

        // Find the user by email
        $user = \App\Models\User::where('email', $validated['email'])->first();

        if (!$user) {
            Log::warning('Password reset attempt for non-existent email: ' . $validated['email']);
            return response()->json(['message' => 'The email address does not exist in our records.'], 404);
        }

        // Update the user's password
        $user->password = Hash::make($validated['password']);
        $user->save();

        Log::info('Password reset successfully for: ' . $validated['email']);
        return response()->json(['message' => 'Password reset successfully.'], 200);
    }

}
