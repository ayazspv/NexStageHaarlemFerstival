<?php

namespace App\Http\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode; 

require base_path('vendor/autoload.php'); // Ensure the correct path to autoload.php

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
                // Generate the QR code as a PNG image
                $qrCodeImage = QrCode::format('png')->size(200)->generate($qrCodeNumber);

                // Save the QR code to a temporary file
                $tempFilePath = sys_get_temp_dir() . "/qr_code_{$index}.png";
                file_put_contents($tempFilePath, $qrCodeImage);

                // Attach the QR code image to the email
                $mail->addAttachment($tempFilePath, "QR_Code_{$qrCodeNumber}.png");
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
