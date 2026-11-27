# Student module (prep school edition)

A small Yii2 module that stores each student in two tables:

| Table | Holds |
|---|---|
| `stu_info` | identity, contact, admission date, father, mother, guardian, address, previous school, emergency contact |
| `stu_master` | class placement (course, batch, section, branch), login user id, audit columns, soft-delete flag |

* **No field is mandatory** (form, model rules and database columns).
* **No foreign keys.** The tables link to each other by plain id columns.
* Empty text is stored as `NULL`, so unique columns (Aadhaar, email, registration no.) never clash on blanks.

## Install

1. Copy this folder to `modules/student` in your Yii2 app (namespace `app\modules\student`).
2. Create the tables, with **either**
   * `mysql your_db < modules/student/sql/schema.sql`, or
   * `php yii migrate --migrationPath=@app/modules/student/migrations`
3. Register the module in `config/web.php`:

```php
'modules' => [
    'student' => [
        'class' => 'app\modules\student\StudentModule',
        // all optional:
        'registrationPrefix' => 'T',       // T202600001
        'pageSize' => 20,
        'createLogin' => false,            // true = also create app\models\User + AuthAssignment
        'loginRole' => 'Student',
        'classLists' => [                  // turns the number inputs into dropdowns
            'course'  => function () { /* return [id => label] */ return []; },
            'batch'   => [1 => '2026-27'],
            'section' => [1 => 'A', 2 => 'B'],
            'branch'  => [],
        ],
    ],
],
```

4. Open `/student` (or `index.php?r=student`).

## Screens

* **List** (`/student/stu-master`): filter by name, registration no., gender, class, mobile, status; sortable; CSV export of the filtered rows.
* **Add / Update**: one form with sections (student, class placement, father, mother, guardian, address, previous school, emergency contact).
* **View**: read-only profile.
* **Delete** moves the student to **Trash** (`is_status = 2`); Trash has a **Restore** button.

## How saving works

Create runs in one transaction: save `stu_info` -> generate the registration number
(unless one was typed) -> optionally create the login -> save `stu_master` -> write the
master id back into `stu_info.stu_info_stu_master_id`. Any error rolls everything back.

## Things to know

* **Access:** the controller allows any logged-in user. Add RBAC roles in `behaviors()` (`StuMasterController`) before going live; this module holds data about children.
* **Login creation** is off by default. When on, it needs `app\models\User` (fields `user_login_id`, `user_password`, `user_type`, `created_by`, `created_at`) and `app\models\AuthAssignment`, and prints a one-time random password. If your app verifies passwords differently, set `passwordHasher` to a callable.
* **Table names** are fixed (`stu_info`, `stu_master`); Yii table prefixes are not supported.
* Dates are stored as `YYYY-MM-DD` and entered with the browser's date picker.
* Phone numbers are strings (7-15 chars, optional leading `+`).
* Uniqueness is enforced for Aadhaar, student email and registration no. only when a value is entered. Parent emails may repeat (siblings).
* Not included: guardians as separate rows, siblings, documents, health records, fees, attendance. Add them as new tables/tabs when needed.
