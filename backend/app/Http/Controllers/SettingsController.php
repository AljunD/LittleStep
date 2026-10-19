<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDisplayRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateTeacherProfileRequest;
use App\Models\Teacher;
use App\Notifications\PasswordChangedNotification;
use App\Services\TeacherPasswordService;
use App\Support\DisplayTheme;
use App\Support\FormRules;
use App\Support\SafeMail;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function profile(Request $request)
    {
        return view('settings.profile', [
            'user' => $request->user(),
            'teacher' => $this->currentTeacher($request),
        ]);
    }

    public function editProfile(Request $request)
    {
        $teacher = $this->currentTeacher($request);

        return view('settings.profile-edit', [
            'user' => $request->user(),
            'teacher' => $teacher,
            'address' => FormRules::splitAddress($teacher->address),
        ]);
    }

    public function updateProfile(UpdateTeacherProfileRequest $request)
    {
        $teacher = $this->currentTeacher($request)->fill(FormRules::teacherAttributes($request->validated()));

        if (! $teacher->isDirty()) {
            return redirect()->route('settings.profile')->with('status', 'No changes to save.');
        }

        $changedFields = $this->changedFieldsList($teacher);
        $teacher->save();

        recordLog('updated', 'Teacher', $teacher->id, 'Teacher updated their profile: '.$changedFields.'.');

        return redirect()->route('settings.profile')->with('success', 'Your profile has been updated.');
    }

    public function help()
    {
        return view('settings.help');
    }

    public function updatePassword(UpdatePasswordRequest $request, TeacherPasswordService $passwords)
    {
        $user = $request->user();

        // New session ID first (the old one is destroyed), so it is the only session kept.
        $request->session()->regenerate(true);
        $passwords->change($user, $request->validated('password'), $request->session()->getId());

        recordLog('password_changed', 'User', $user->id, 'Teacher changed their password.');

        $redirect = redirect()->route('settings.profile')
            ->with('success', 'Your password has been changed. Other devices signed in to your account have been signed out.');

        if (! SafeMail::attempt(fn () => $user->notify(new PasswordChangedNotification(now())))) {
            $redirect->with('error', 'The confirmation email could not be sent because the mail server could not be reached. Your new password is still saved.');
        }

        return $redirect;
    }

    public function display(Request $request)
    {
        return view('settings.display', [
            'currentTheme' => DisplayTheme::current($request),
            'themes' => DisplayTheme::OPTIONS,
        ]);
    }

    public function updateDisplay(UpdateDisplayRequest $request)
    {
        $theme = $request->validated('theme');

        recordLog('updated', 'User', $request->user()->id, 'Display theme set to '.DisplayTheme::OPTIONS[$theme].'.');

        return redirect()->route('settings.display')
            ->withCookie(cookie()->forever(DisplayTheme::COOKIE, $theme))
            ->with('success', 'Display preference saved.');
    }

    private function currentTeacher(Request $request): Teacher
    {
        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 403, 'A teacher profile is required.');

        return $teacher;
    }
}
