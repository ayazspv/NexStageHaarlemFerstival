<?php

namespace App\Http\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;

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

            $options = new QROptions;

            $options->outputType       = QROutputInterface::FPDF;
            $options->scale            = 5;
            $options->fpdfMeasureUnit  = 'mm'; // pt, mm, cm, in
            $options->bgColor          = [222, 222, 222]; // [R, G, B]
            $options->drawLightModules = false;
            $options->moduleValues     = [
                QRMatrix::M_FINDER_DARK    => [0, 63, 255],    // dark (true)
                QRMatrix::M_FINDER_DOT     => [0, 63, 255],    // finder dot, dark (true)
                QRMatrix::M_FINDER         => [255, 255, 255], // light (false)
                QRMatrix::M_ALIGNMENT_DARK => [255, 0, 255],
                QRMatrix::M_ALIGNMENT      => [255, 255, 255],
                QRMatrix::M_DATA_DARK      => [0, 0, 0],
                QRMatrix::M_DATA           => [255, 255, 255],
            ];

            // Generate QR codes and attach them to the email
            foreach ($validated['qrCodesNumber'] as $index => $qrCodeNumber) {
                try {
                    // Generate the QR code as a PDF image
                    $qrcode = (new QRCode($options))->render($qrCodeNumber);

                    // Save the QR code to a temporary file
                    $tempFilePath = sys_get_temp_dir() . "/qr_code_{$index}.pdf";
                    file_put_contents($tempFilePath, $qrcode);

                    // Attach the QR code image to the email
                    $mail->addAttachment($tempFilePath, "QR_Code_{$qrCodeNumber}.pdf");
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
