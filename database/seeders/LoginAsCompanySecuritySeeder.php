<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;
use App\Models\UserEmailTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class LoginAsCompanySecuritySeeder extends Seeder
{
    /**
     * Seed the "Login As Company Security OTP" and "Disable Security OTP" email templates.
     */
    public function run(): void
    {
        $templates = [
            [
                'name'    => 'Login As Company Security OTP',
                'slug'    => 'login_as_company_otp',
                'subject' => 'Security Verification OTP for Login As Company',
                'content' => '<p>Hello <strong>{company_name}</strong>,</p>
                              <p>A Super Admin is requesting to access your account via <strong>Login As Company</strong>.</p>
                              <p>Your one-time security OTP is: <strong style="font-size: 22px; color: #0d6efd; letter-spacing: 3px; background: #eef2ff; padding: 4px 12px; border-radius: 4px;">{otp_code}</strong></p>
                              <p>This OTP is valid for <strong>{otp_expires_minutes} minutes</strong>. Please share this OTP with the administrator requesting access.</p>
                              <p>Thank you,<br>{app_name} Security Team</p>',
            ],
            [
                'name'    => 'Disable Login As Company Security OTP',
                'slug'    => 'disable_login_as_company_security_otp',
                'subject' => 'Security Verification OTP to Disable Login As Company Security',
                'content' => '<p>Hello <strong>{user_name}</strong>,</p>
                              <p>You have requested to <strong>disable the "Login As Company Security" setting</strong> in HRMS Super Admin settings.</p>
                              <p>Your security verification OTP code is: <strong style="font-size: 22px; color: #dc3545; letter-spacing: 3px; background: #ffeef0; padding: 4px 12px; border-radius: 4px;">{otp_code}</strong></p>
                              <p>This OTP is valid for <strong>{otp_expires_minutes} minutes</strong>. Enter this code in the settings popup to confirm disabling security.</p>
                              <p>If you did not request this change, please secure your account immediately.</p>
                              <p>Thank you,<br>{app_name} Security Team</p>',
            ],
        ];

        foreach ($templates as $tpl) {
            // 1. Create or update EmailTemplate row
            $emailTemplate = EmailTemplate::firstOrCreate(
                ['slug' => $tpl['slug']],
                [
                    'name'       => $tpl['name'],
                    'from'       => 'HRMS',
                    'created_by' => 1,
                ]
            );

            // 2. Create or update English lang content
            EmailTemplateLang::firstOrCreate(
                [
                    'parent_id' => $emailTemplate->id,
                    'lang'      => 'en',
                ],
                [
                    'subject' => $tpl['subject'],
                    'content' => $tpl['content'],
                ]
            );

            // 3. Create UserEmailTemplate for all existing super admin & company users
            $users = User::whereIn('type', ['company', 'super admin'])->get();

            foreach ($users as $user) {
                UserEmailTemplate::firstOrCreate(
                    [
                        'template_id' => $emailTemplate->id,
                        'user_id'     => $user->id,
                    ],
                    [
                        'is_active' => 1,
                    ]
                );
            }
        }
    }
}
