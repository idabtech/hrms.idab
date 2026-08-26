<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Task statuses
    |--------------------------------------------------------------------------
    | The status of tasks in the self assessment.
    */
    'statuses' => [
        'completed'   => 'Completed',
        'in_progress' => 'In Progress',
        'pending'     => 'Pending',
    ],

    /*
    |--------------------------------------------------------------------------
    | Task priorities
    |--------------------------------------------------------------------------
    */
    'priorities' => [
        'high'   => 'High',
        'medium' => 'Medium',
        'low'    => 'Low',
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance areas rated by the reporting manager
    |--------------------------------------------------------------------------
    */
    'performance_areas' => [
        'Technical knowledge',
        'Troubleshooting & problem solving',
        'Task completion',
        'Quality of work',
        'Time management',
        'User / customer support',
        'Communication',
        'Teamwork & collaboration',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rating scale
    |--------------------------------------------------------------------------
    */
    'scale' => [
        1 => 'Needs Improvement',
        2 => 'Below Expectations',
        3 => 'Meets Expectations',
        4 => 'Exceeds Expectations',
        5 => 'Outstanding',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rows offered on a blank form
    |--------------------------------------------------------------------------
    */
    'default_task_rows' => 5,
    'max_task_rows'     => 20,
];
