<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;
use App\Models\UserEmailTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalaryRevisionEmailTemplateSeeder extends Seeder
{
    /**
     * Seed the two salary-revision email templates and their English lang content.
     * Safe to run multiple times — uses firstOrCreate / updateOrCreate.
     */
    public function run(): void
    {
        $templates = [
            [
                'name'    => 'Salary Revision Created',
                'slug'    => 'salary_revision_created',
                'subject' => 'New Salary Revision Scheduled',
                'content' => '<p><strong>Subject: New Salary Revision Scheduled for Review</strong></p>
                                <p>A salary revision has been scheduled for employee <strong>{revision_employee_name}</strong>. Please review and approve.</p>
                                <p><strong>Current Salary:</strong> {revision_current_salary}</p>
                                <p><strong>Revised Salary:</strong> {revision_revised_salary}</p>
                                <p><strong>Revision Type:</strong> {revision_type}</p>
                                <p><strong>Value:</strong> {revision_value}</p>
                                <p><strong>Effective From:</strong> {revision_effective_from}</p>
                                <p><strong>Cycle:</strong> {revision_cycle}</p>
                                <p><strong>Note:</strong> {revision_note}</p>
                                <p>Please log in to the system to approve this revision.</p>
                                <p>Thank you,</p>
                                <p>HR Department,</p>
                                <p>{company_name}</p>',
            ],
            [
                'name'    => 'Salary Revision Updated',
                'slug'    => 'salary_revision_updated',
                'subject' => 'Salary Revision Updated',
                'content' => '<p><strong>Subject: Salary Revision Has Been Updated</strong></p>
                                <p>The salary revision for employee <strong>{revision_employee_name}</strong> has been updated by <strong>{revision_updated_by}</strong>.</p>
                                <p><strong>Current Salary:</strong> {revision_current_salary}</p>
                                <p><strong>Revised Salary:</strong> {revision_revised_salary}</p>
                                <p><strong>Revision Type:</strong> {revision_type}</p>
                                <p><strong>Value:</strong> {revision_value}</p>
                                <p><strong>Effective From:</strong> {revision_effective_from}</p>
                                <p><strong>Cycle:</strong> {revision_cycle}</p>
                                <p><strong>Note:</strong> {revision_note}</p>
                                <p>Please log in to the system to review the updated revision.</p>
                                <p>Thank you,</p>
                                <p>HR Department,</p>
                                <p>{company_name}</p>',
            ],
            [
                'name'    => 'Salary Revision Approved',
                'slug'    => 'salary_revision_approved',
                'subject' => 'Salary Revision Approved',
                'content' => '<p><strong>Subject: Salary Revision Has Been Approved</strong></p>
                                <p>The salary revision for employee <strong>{revision_employee_name}</strong> has been approved by <strong>{revision_approved_by}</strong> and will be applied automatically on the effective date.</p>
                                <p><strong>Current Salary:</strong> {revision_current_salary}</p>
                                <p><strong>Revised Salary:</strong> {revision_revised_salary}</p>
                                <p><strong>Revision Type:</strong> {revision_type}</p>
                                <p><strong>Value:</strong> {revision_value}</p>
                                <p><strong>Effective From:</strong> {revision_effective_from}</p>
                                <p><strong>Cycle:</strong> {revision_cycle}</p>
                                <p><strong>Note:</strong> {revision_note}</p>
                                <p>The new salary will be applied automatically by the scheduled job on the effective date.</p>
                                <p>Thank you,</p>
                                <p>HR Department,</p>
                                <p>{company_name}</p>',
            ],
            [
                'name'    => 'Salary Revision Applied',
                'slug'    => 'salary_revision_applied',
                'subject' => 'Salary Revision Applied',
                'content' => '<p><strong>Subject: Salary Revision Has Been Applied</strong></p>
                                <p>The approved salary revision for employee <strong>{revision_employee_name}</strong> has been applied automatically by the scheduled job.</p>
                                <p><strong>Previous Salary:</strong> {revision_current_salary}</p>
                                <p><strong>New Salary:</strong> {revision_revised_salary}</p>
                                <p><strong>Revision Type:</strong> {revision_type}</p>
                                <p><strong>Value:</strong> {revision_value}</p>
                                <p><strong>Effective From:</strong> {revision_effective_from}</p>
                                <p><strong>Note:</strong> {revision_note}</p>
                                <p>Feel free to reach out if you have any questions.</p>
                                <p>Thank you,</p>
                                <p>HR Department,</p>
                                <p>{company_name}</p>',
            ],
            [
                'name'    => 'Salary Revision Rejected',
                'slug'    => 'salary_revision_rejected',
                'subject' => 'Salary Revision Rejected',
                'content' => '<p><strong>Subject: Salary Revision Has Been Rejected</strong></p>
                                <p>The salary revision scheduled for employee <strong>{revision_employee_name}</strong> has been rejected by <strong>{revision_rejected_by}</strong>.</p>
                                <p><strong>Current Salary:</strong> {revision_current_salary}</p>
                                <p><strong>Proposed Revised Salary:</strong> {revision_revised_salary}</p>
                                <p><strong>Revision Type:</strong> {revision_type}</p>
                                <p><strong>Value:</strong> {revision_value}</p>
                                <p><strong>Effective From:</strong> {revision_effective_from}</p>
                                <p><strong>Note:</strong> {revision_note}</p>
                                <p>Please contact your administrator for further information.</p>
                                <p>Thank you,</p>
                                <p>HR Department,</p>
                                <p>{company_name}</p>',
            ],
        ];

        foreach ($templates as $tpl) {
            // 1. Create the EmailTemplate row if it doesn't exist
            $emailTemplate = EmailTemplate::firstOrCreate(
                ['slug' => $tpl['slug']],
                [
                    'name'       => $tpl['name'],
                    'created_by' => 1,
                ]
            );

            // 2. Create the English lang content if it doesn't exist
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

            // 3. Activate the template for every existing company user
            $companyUsers = User::whereIn('type', ['company', 'super admin'])->get();

            foreach ($companyUsers as $user) {
                UserEmailTemplate::firstOrCreate(
                    [
                        'template_id' => $emailTemplate->id,
                        'user_id'     => $user->id,
                    ],
                    ['is_active' => 1]
                );
            }
        }
    }
}
