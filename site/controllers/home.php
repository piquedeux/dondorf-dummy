<?php

use Uniform\Form;

return function ($kirby, $page) {
    // Initialize booking form
    $bookingForm = new Form([
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

    // Initialize newsletter form
    $newsletterForm = new Form([
        'newsletter_email' => [
            'rules' => ['required', 'email'],
            'message' => 'E-Mail ist erforderlich',
        ],
    ]);

    // Handle booking form submission
    if ($kirby->request()->is('POST') && !isset($_POST['newsletter_submit'])) {
        try {
            $bookingForm->validate();

            $data = $bookingForm->data();
            $name = $data['name'];
            $email = $data['email'];
            $phone = $data['phone'] ?? '';
            $date = $data['date'];
            $guests = $data['guests'] ?? '';
            $message = $data['message'] ?? '';

            // Create unique slug
            $slug = \Kirby\Toolkit\Str::slug($name . '-' . $date . '-' . time());

            // Ensure bookings parent exists
            $parent = site()->find('bookings');
            if (!$parent) {
                $parent = site()->createChild([
                    'slug' => 'bookings',
                    'template' => 'bookings',
                    'content' => ['title' => 'Anfragen'],
                    'isDraft' => false
                ]);
            }

            // Create booking page
            $parent->createChild([
                'slug'     => $slug,
                'template' => 'booking',
                'content'  => [
                    'title'   => $name . ' — ' . $date,
                    'name'    => $name,
                    'email'   => $email,
                    'phone'   => $phone,
                    'date'    => $date,
                    'guests'  => $guests,
                    'message' => $message,
                    'status'  => 'pending'
                ],
                'isDraft'  => false
            ]);

            // Send notification email to all editors (excluding specific admin)
            try {
                $editorUsers = kirby()->users()->filter(function($user) {
                    return $user->role()->name() === 'editor' && $user->email() !== 'admin@example.com';
                });
                
                $adminEmails = $editorUsers->pluck('email');
                
                error_log('Editor users found: ' . $editorUsers->count());
                error_log('Editor emails: ' . print_r($adminEmails, true));
                
                if (!empty($adminEmails)) {
                    foreach ($adminEmails as $adminEmail) {
                        try {
                            kirby()->email([
                                'from' => 'no-reply@example.com',
                                'to' => $adminEmail,
                                'subject' => 'Neue Veranstaltungsanfrage eingegangen',
                                'body' => "Eine neue Veranstaltungsanfrage ist im Panel unter 'Buchungen' eingegangen:\n\n" .
                                          "Name: " . $name . "\n" .
                                          "E-Mail: " . $email . "\n" .
                                          "Telefon: " . $phone . "\n" .
                                          "Datum: " . $date . "\n" .
                                          "Anzahl Gäste: " . $guests . "\n" .
                                          "Nachricht: " . $message . "\n\n" .
                                          "Zeit: " . date('d.m.Y H:i:s') . "\n" .
                                          "Bitte im Panel unter 'Buchungen' prüfen und bearbeiten."
                            ]);
                            error_log('Email sent successfully to: ' . $adminEmail);
                        } catch (Exception $e) {
                            error_log('Failed to send email to ' . $adminEmail . ': ' . $e->getMessage());
                        }
                    }
                } else {
                    error_log('No editor users found for booking notifications!');
                }
            } catch (Exception $emailError) {
                error_log('Booking email error: ' . $emailError->getMessage());
            }

            // Send confirmation email to customer
            try {
                kirby()->email([
                    'from' => 'no-reply@example.com',
                    'to' => $email,
                    'subject' => 'Bestätigung Ihrer Veranstaltungsanfrage',
                    'body' => "Hallo " . $name . ",\n\n" .
                              "vielen Dank für Ihre Anfrage. Wir haben Ihre Nachricht erhalten und werden uns in Kürze bei Ihnen melden.\n\n" .
                              "Ihre Angaben:\n" .
                              "Datum: " . $date . "\n" .
                              "Anzahl Gäste: " . $guests . "\n" .
                              "Nachricht: " . $message . "\n\n" .
                              "Bei Fragen erreichen Sie uns unter no-reply@example.com.\n\n" .
                              "Beste Grüße\n" .
                              "Ihr Team von 2og Dondorf"
                ]);
                error_log('Confirmation email sent to customer: ' . $email);
            } catch (Exception $e) {
                error_log('Failed to send confirmation to customer ' . $email . ': ' . $e->getMessage());
            }

            $bookingForm->success('Vielen Dank! Ihre Anfrage wurde erfolgreich übermittelt.');
        } catch (Exception $e) {
            $bookingForm->error($e->getMessage());
        }
    }

    // Handle newsletter form submission
    if ($kirby->request()->is('POST') && isset($_POST['newsletter_submit'])) {
        try {
            $newsletterForm->validate();

            $data = $newsletterForm->data();
            $email = $data['newsletter_email'];

            // Create unique slug
            $slug = \Kirby\Toolkit\Str::slug($email . '-' . time());

            // Ensure newsletter parent exists
            $parent = site()->find('newsletter');
            if (!$parent) {
                $parent = site()->createChild([
                    'slug' => 'newsletter',
                    'template' => 'newsletter',
                    'content' => ['title' => 'Newsletter-Abonnenten'],
                    'isDraft' => false
                ]);
            }

            // Create newsletter subscription page
            $parent->createChild([
                'slug'     => $slug,
                'template' => 'newsletter-subscription',
                'content'  => [
                    'title' => $email,
                    'email' => $email
                ],
                'isDraft'  => false
            ]);

            // Send notification to all editors (excluding specific admin)
            try {
                $editorUsers = kirby()->users()->filter(function($user) {
                    return $user->role()->name() === 'editor' && $user->email() !== 'admin@example.com';
                });
                
                $adminEmails = $editorUsers->pluck('email');
                
                error_log('Editor users found for newsletter: ' . $editorUsers->count());
                error_log('Editor emails for newsletter: ' . print_r($adminEmails, true));
                
                if (!empty($adminEmails)) {
                    foreach ($adminEmails as $adminEmail) {
                        try {
                            kirby()->email([
                                'from' => 'no-reply@example.com',
                                'to' => $adminEmail,
                                'subject' => 'Neue Newsletter-Anmeldung eingegangen',
                                'body' => "Eine neue Newsletter-Anmeldung ist im Panel eingegangen:\n\n" .
                                          "E-Mail: " . $email . "\n" .
                                          "Zeit: " . date('d.m.Y H:i:s') . "\n\n" .
                                          "Bitte im Panel unter 'Newsletter' prüfen."
                            ]);
                            error_log('Newsletter notification sent to: ' . $adminEmail);
                        } catch (Exception $e) {
                            error_log('Failed to send newsletter notification to ' . $adminEmail . ': ' . $e->getMessage());
                        }
                    }
                } else {
                    error_log('No editor users found for newsletter notifications!');
                }
            } catch (Exception $emailError) {
                error_log('Newsletter email error: ' . $emailError->getMessage());
            }

            // Send confirmation email to subscriber
            try {
                kirby()->email([
                    'from' => 'no-reply@example.com',
                    'to' => $email,
                    'subject' => 'Willkommen bei unserem Newsletter',
                    'body' => "Hallo,\n\n" .
                              "vielen Dank für die Anmeldung zu unserem Newsletter!\n\n" .
                              "Sie erhalten ab sofort Updates zu unseren Veranstaltungen und Neuigkeiten.\n\n" .
                              "Bei Fragen erreichen Sie uns unter no-reply@example.com.\n\n" .
                              "Beste Grüße\n" .
                              "Ihr Team von 2og Dondorf"
                ]);
                error_log('Newsletter confirmation sent to: ' . $email);
            } catch (Exception $e) {
                error_log('Failed to send newsletter confirmation to ' . $email . ': ' . $e->getMessage());
            }

            $newsletterForm->success('Vielen Dank! Du erhältst ab sofort Updates.');
        } catch (Exception $e) {
            $newsletterForm->error($e->getMessage());
        }
    }

    return compact('bookingForm', 'newsletterForm');
};
