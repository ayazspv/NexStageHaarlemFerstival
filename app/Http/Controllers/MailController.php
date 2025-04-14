<?php

namespace App\Http\Controllers;

require_once base_path('vendor/autoload.php');

use Endroid\QrCode\Builder\Builder;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;


class MailController
{
    public function sendMail(Request $request)
    {
             // Validate the incoming request
        $validated = $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'altBody' => 'nullable|string',
            'qrCodesNumber' => 'required|array', // Ensure qrCodesNumber is an array
        ]);

        // Initialize PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST'); // SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME'); // SMTP username
            $mail->Password = env('MAIL_PASSWORD'); // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = env('MAIL_PORT');

            // Recipients
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($validated['to']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $validated['subject'];
            $mail->Body = $validated['body'];
            $mail->AltBody = $validated['altBody'] ?? '';

            

            // Generate QR codes and attach them to the email
            foreach ($validated['qrCodesNumber'] as $index => $qrCodeNumber) {
                try {

                    // 1. Generate QR Code
                    $qrResult = Builder::create()
                        ->data('https://example.com') // Change this to your target URL or text
                        ->size(300)
                        ->margin(10)
                        ->build();

                    // Save QR image to temp file
                    $qrPath = sys_get_temp_dir() . '/qrcode.png';
                    file_put_contents($qrPath, $qrResult->getString());

                   

                    // Attach the QR code image to the email
                    $mail->addAttachment($qrPath, "QR_Code_{$qrCodeNumber}.png");
                } catch (\Exception $e) {
                    Log::error("Error generating QR code for {$qrCodeNumber}: {$e->getMessage()}");
                }
            }

            // Send the email
            $mail->send();

            // Return a success response
            return response()->json(['message' => 'Email sent successfully with QR codes'], 200);
        } catch (Exception $e) {
            // Log the error and return a failure response
            Log::error("Mail error: {$e->getMessage()}");
            return response()->json(['error' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
      
    }
}
