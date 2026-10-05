<?php

use Uniform\Form;

return function ($kirby, $page) {
    $form = new Form([
        'name' => [
            'rules' => ['required'],
            'message' => 'Name ist erforderlich',
        ],
        'email' => [
            'rules' => ['required', 'email'],
            'message' => 'E-Mail ist erforderlich',
        ],
        'phone' => [],
        'date' => [
            'rules' => ['required'],
            'message' => 'Datum ist erforderlich',
        ],
        'guests' => [],
        'message' => [],
    ]);

    if ($kirby->request()->is('POST')) {
        try {
            $form->validate();

            // Get form data
            $data = $form->data();

            // Create unique slug
            $title = trim($data['name']) . ' — ' . trim($data['date']);
            $slug = \Kirby\Toolkit\Str::slug($title . '-' . time());

            // Prepare content for booking page
            $content = [
                'title'    => $title,
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'] ?? '',
                'date'     => $data['date'],
                'guests'   => $data['guests'] ?? '',
                'message'  => $data['message'] ?? '',
                'status'   => 'pending'
            ];

            // Ensure bookings parent exists
            $bookingsParentSlug = site()->bookings_parent_slug()->or('bookings');
            $parent = site()->find($bookingsParentSlug);
            if (!$parent) {
                $parent = site()->createChild([
                    'slug' => 'bookings',
                    'template' => 'bookings',
                    'content' => ['title' => 'Anfragen'],
                    'isDraft' => false
                ]);
            }

            // Create booking page under parent
            $bookingPage = $parent->createChild([
                'slug'     => $slug,
                'template' => 'booking',
                'content'  => $content,
                'isDraft'  => false
            ]);

            // Send notification email to all editors (excluding specific admin)
            try {
                $editorUsers = kirby()->users()->filter(function($user) {
                    return $user->role()->name() === 'editor' && $user->email() !== 'admin@example.com';
                });
                
                $adminEmails = $editorUsers->pluck('email');
                
                error_log('Editor users found (booking-form): ' . $editorUsers->count());
                error_log('Editor emails (booking-form): ' . print_r($adminEmails, true));
                
                if (!empty($adminEmails)) {
                    foreach ($adminEmails as $adminEmail) {
                        try {
                            kirby()->email([
                                'from' => 'no-reply@example.com',
                                'to' => $adminEmail,
                                'subject' => 'Neue Veranstaltungsanfrage eingegangen',
                                'body' => "Eine neue Veranstaltungsanfrage ist im Panel unter 'Buchungen' eingegangen:\n\n" .
                                          "Name: " . $data['name'] . "\n" .
                                          "E-Mail: " . $data['email'] . "\n" .
                                          "Telefon: " . ($data['phone'] ?? '-') . "\n" .
                                          "Datum: " . $data['date'] . "\n" .
                                          "Gäste: " . ($data['guests'] ?? '-') . "\n" .
                                          "Nachricht: " . ($data['message'] ?? '-') . "\n\n" .
                                          "Bitte im Panel unter 'Buchungen' prüfen und bearbeiten."
                            ]);
                            error_log('Email sent successfully to: ' . $adminEmail);
                        } catch (Exception $e) {
                            error_log('Failed to send email to ' . $adminEmail . ': ' . $e->getMessage());
                            error_log('Stack trace: ' . $e->getTraceAsString());
                        }
                    }
                } else {
                    error_log('No editor emails found!');
                }
            } catch (Exception $emailError) {
                error_log('Email error: ' . $emailError->getMessage());
                error_log('Stack trace: ' . $emailError->getTraceAsString());
            }

            // Send confirmation email to customer
            try {
                kirby()->email([
                    'from' => 'no-reply@example.com',
                    'to' => $data['email'],
                    'subject' => 'Bestätigung Ihrer Veranstaltungsanfrage',
                    'body' => "Hallo " . $data['name'] . ",\n\n" .
                              "vielen Dank für Ihre Anfrage. Wir haben Ihre Nachricht erhalten und werden uns in Kürze bei Ihnen melden.\n\n" .
                              "Ihre Angaben:\n" .
                              "Datum: " . $data['date'] . "\n" .
                              "Anzahl Gäste: " . ($data['guests'] ?? '-') . "\n" .
                              "Nachricht: " . ($data['message'] ?? '-') . "\n\n" .
                              "Bei Fragen erreichen Sie uns unter no-reply@example.com.\n\n" .
                              "Beste Grüße\n" .
                              "Ihr Team von 2og Dondorf"
                ]);
                error_log('Confirmation email sent to customer: ' . $data['email']);
            } catch (Exception $e) {
                error_log('Failed to send confirmation to customer ' . $data['email'] . ': ' . $e->getMessage());
            }

            $form->success('Vielen Dank! Ihre Veranstaltungsanfrage wurde erfolgreich versendet.');
        } catch (Exception $e) {
            $form->error($e->getMessage());
        }
    }

    return ['bookingForm' => $form];
};
