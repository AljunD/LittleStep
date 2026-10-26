<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexSearchRequest;
use App\Models\Guardian;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use App\Support\FormRules;
use App\Support\SafeMail;
use App\Support\Search;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GuardianController extends Controller
{
    protected function validationMessages()
    {
        return array_merge([
            'first_name.required' => 'The first name field is required.',
            'last_name.required' => 'The last name field is required.',
            'sex.required' => 'Please select the guardian\'s sex.',
            'sex.in' => 'The selected sex must be either Male or Female.',
            'contact_number.required' => 'The contact number field is required.',
            'relationship_to_child.required' => 'The relationship to child field is required.',
            'email.required' => 'The email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered to another user account.',
            'password.min' => 'The password must be at least 12 characters long.',
            'password.confirmed' => 'The password confirmation does not match the password.',

            'child_first_name.required' => 'The child\'s first name is required.',
            'child_last_name.required' => 'The child\'s last name is required.',
            'child_sex.required' => 'Please select the child\'s sex.',
            'child_date_of_birth.required' => 'The child\'s date of birth is required.',
            'child_handedness.required' => 'Please select child\'s handedness.',
            'photo.image' => 'The uploaded file must be an image.',
        ], FormRules::messages());
    }

    private function guardianRules(bool $required): array
    {
        return [
            'first_name' => FormRules::name($required),
            'middle_name' => FormRules::name(false),
            'last_name' => FormRules::name($required),
            'sex' => [$required ? 'required' : 'nullable', Rule::in(FormRules::SEXES)],
            'contact_number' => FormRules::contactNumber($required),
            'relationship_to_child' => FormRules::name($required),
            'barangay' => ['nullable', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function index(IndexSearchRequest $request)
    {
        $guardians = Search::apply($this->currentTeacher($request)->ownedGuardians(), $request->search(), [
            'first_name', 'middle_name', 'last_name', 'contact_number', 'relationship_to_child', 'user.email',
        ])
            ->with(['user', 'children' => function ($query) use ($request) {
                $query->whereHas('progressRecords', function ($progressQuery) use ($request) {
                    $progressQuery->withTrashed()->where('teacher_id', $request->user()->teacher->id);
                });
            }])
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();

        return view('guardians.index', compact('guardians'));
    }

    public function create()
    {
        return view('guardians.create', [
            'defaultPassword' => config('littlestep.guardian_default_password'),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);
        $validated = $request->validate(array_merge(
            [
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => FormRules::password(false),
            ],
            $this->guardianRules(true),
            FormRules::childProfile('child_'),
        ), $this->validationMessages());

        $teacher = $this->currentTeacher($request);
        $usesDefaultPassword = empty($validated['password']);
        $password = $usesDefaultPassword ? (string) config('littlestep.guardian_default_password') : $validated['password'];
        $isStudying = (bool) ($validated['is_studying'] ?? false);

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('photos/children', 'local')
            : null;

        $user = DB::transaction(function () use ($validated, $teacher, $password, $isStudying, $photoPath) {
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'role' => 'guardian',
            ]);

            $guardian = Guardian::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'sex' => $validated['sex'],
                'contact_number' => $validated['contact_number'],
                'address' => FormRules::joinAddress($validated),
                'relationship_to_child' => $validated['relationship_to_child'],
            ]);

            $child = $guardian->children()->create([
                'first_name' => $validated['child_first_name'],
                'middle_name' => $validated['child_middle_name'] ?? null,
                'last_name' => $validated['child_last_name'],
                'sex' => $validated['child_sex'],
                'date_of_birth' => $validated['child_date_of_birth'],
                'address' => FormRules::joinAddress($validated, 'child_'),
                'handedness' => $validated['child_handedness'],
                'is_studying' => $isStudying,
                'school_name' => $isStudying ? ($validated['school_name'] ?? null) : null,
                'fathers_name' => $validated['fathers_name'] ?? null,
                'fathers_age' => $validated['fathers_age'] ?? null,
                'fathers_occupation' => $validated['fathers_occupation'] ?? null,
                'fathers_education' => $validated['fathers_education'] ?? null,
                'mothers_name' => $validated['mothers_name'] ?? null,
                'mothers_age' => $validated['mothers_age'] ?? null,
                'mothers_occupation' => $validated['mothers_occupation'] ?? null,
                'mothers_education' => $validated['mothers_education'] ?? null,
                'number_of_siblings' => $validated['number_of_siblings'] ?? null,
                'birth_order' => $validated['birth_order'] ?? null,
                'photo_path' => $photoPath,
            ]);

            $assessmentDate = now()->toDateString();
            ProgressRecord::create([
                'child_id' => $child->id,
                'teacher_id' => $teacher->id,
                'evaluation_date' => $assessmentDate,
                'evaluation_date_round_1' => $assessmentDate,
                'evaluation_number' => 1,
                'status' => 'pending',
            ]);

            recordLog('created', 'Guardian', $guardian->id, 'Guardian and child registered: '.$guardian->first_name.' '.$guardian->last_name);

            return $user;
        });

        $verificationSent = SafeMail::attempt(fn () => event(new Registered($user)));

        $message = 'Guardian and child registered successfully.';
        if ($usesDefaultPassword) {
            $message .= ' No password was entered, so the account uses the default password. Ask the guardian to change it after signing in.';
        }

        return redirect()->route('guardians.index')
            ->with('success', $message)
            ->with('error', $verificationSent ? null : SafeMail::VERIFICATION_FAILED);
    }

    public function show(Request $request, $id)
    {
        $teacher = $this->currentTeacher($request);
        $ownedByTeacher = function ($progressQuery) use ($teacher) {
            $progressQuery->withTrashed()->where('teacher_id', $teacher->id);
        };

        // Archived guardians stay viewable from the Archives page, along with their archived children.
        $guardian = Guardian::withTrashed()
            ->whereHas('children', fn ($query) => $query->withTrashed()->whereHas('progressRecords', $ownedByTeacher))
            ->with(['user' => fn ($query) => $query->withTrashed()])
            ->findOrFail($id);
        $guardian->load(['children' => function ($query) use ($guardian, $ownedByTeacher) {
            $query->when($guardian->trashed(), fn ($query) => $query->withTrashed())
                ->whereHas('progressRecords', $ownedByTeacher);
        }]);

        recordLog('viewed', 'Guardian', $guardian->id, 'Guardian record viewed.');

        return view('guardians.show', compact('guardian'));
    }

    public function edit(Request $request, $id)
    {
        $guardian = $this->currentTeacher($request)->ownedGuardians()->findOrFail($id);

        $parts = $guardian->address ? explode(',', $guardian->address) : [];
        $guardian->barangay = isset($parts[0]) ? trim($parts[0]) : '';
        $guardian->municipality = isset($parts[1]) ? trim($parts[1]) : '';
        $guardian->province = isset($parts[2]) ? trim($parts[2]) : '';
        $guardian->region = isset($parts[3]) ? trim($parts[3]) : '';

        return view('guardians.edit', compact('guardian'));
    }

    public function update(Request $request, $id)
    {
        $teacher = $this->currentTeacher($request);
        $guardian = $teacher->ownedGuardians()->with('user')->findOrFail($id);
        abort_if($guardian->children()->withTrashed()
            ->whereDoesntHave('progressRecords', function ($query) use ($teacher) {
                $query->withTrashed()->where('teacher_id', $teacher->id);
            })
            ->exists(), 403, 'This guardian is linked to children assigned to another teacher.');
        if ($request->filled('email')) {
            $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        }

        $data = $request->validate(array_merge($this->guardianRules(false), [
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($guardian->user_id)],
            'password' => FormRules::password(false),
        ]), $this->validationMessages());

        $profile = [];
        foreach (['first_name', 'middle_name', 'last_name', 'sex', 'contact_number', 'relationship_to_child'] as $field) {
            $profile[$field] = ($data[$field] ?? null) ?? $guardian->{$field};
        }
        $profile['address'] = FormRules::joinAddress($data);

        $user = $guardian->user;
        $emailChanged = $user && ! empty($data['email']) && $data['email'] !== $user->email;

        DB::transaction(function () use ($guardian, $user, $profile, $data, $emailChanged) {
            $guardian->update($profile);

            if (! $user) {
                return;
            }

            if ($emailChanged) {
                $user->forceFill(['email' => $data['email'], 'email_verified_at' => null])->save();
            }

            if (! empty($data['password'])) {
                $user->forceFill(['password' => Hash::make($data['password'])])->save();
            }

            if ($emailChanged || ! empty($data['password'])) {
                $user->tokens()->delete();
            }
        });

        $verificationSent = $emailChanged && SafeMail::attempt(fn () => $user->sendEmailVerificationNotification());

        recordLog('updated', 'Guardian', $guardian->id, match (true) {
            ! $emailChanged => 'Guardian updated',
            $verificationSent => 'Guardian updated; email changed and re-verification sent.',
            default => 'Guardian updated; email changed but the verification email could not be sent.',
        });

        $message = 'Guardian updated successfully.';
        if ($verificationSent) {
            $message .= ' A verification link was sent to the new email address; the guardian must verify it before signing in.';
        }

        return redirect()->route('guardians.index')
            ->with('success', $message)
            ->with('error', $emailChanged && ! $verificationSent ? SafeMail::VERIFICATION_FAILED : null);
    }

    public function resendVerification(Request $request, $id)
    {
        $guardian = $this->currentTeacher($request)->ownedGuardians()->with('user')->findOrFail($id);
        $user = $guardian->user;

        if (! $user || $user->hasVerifiedEmail()) {
            return redirect()->route('guardians.index')
                ->with('success', 'This guardian\'s email address is already verified.');
        }

        if (! SafeMail::attempt(fn () => $user->sendEmailVerificationNotification())) {
            return redirect()->route('guardians.index')->with('error', SafeMail::VERIFICATION_FAILED);
        }

        recordLog('verification_resent', 'Guardian', $guardian->id, 'Verification email sent again.');

        return redirect()->route('guardians.index')
            ->with('success', 'A new verification link was sent to '.$user->email.'.');
    }

    public function destroy(Request $request, $id)
    {
        $teacher = $this->currentTeacher($request);
        $guardian = $teacher->ownedGuardians()->with('user')->findOrFail($id);
        $ownedChildren = $teacher->ownedChildren()
            ->where('guardian_id', $guardian->id)
            ->get();
        $ownedChildIds = $teacher->ownedChildren()
            ->withTrashed()
            ->where('guardian_id', $guardian->id)
            ->pluck('children.id');

        foreach ($ownedChildren as $child) {
            $child->delete();

            recordLog('deleted', 'Child', $child->id, 'Child archived with Guardian: '.$child->first_name.' '.$child->last_name);
        }

        $guardianArchived = ! $guardian->children()->withTrashed()
            ->whereNotIn('id', $ownedChildIds)
            ->exists();
        if ($guardianArchived) {
            $guardian->delete();
            $guardian->user?->delete();
        }

        if ($guardianArchived) {
            recordLog('deleted', 'Guardian', $guardian->id, 'Guardian archived: '.$guardian->first_name.' '.$guardian->last_name);
        }

        return redirect()->route('guardians.index')
            ->with('success', $guardianArchived
                ? 'Guardian and assigned children archived successfully.'
                : 'Assigned children archived successfully; the guardian still has other active linked children.');
    }

    public function createChild(Request $request, $id)
    {
        $guardian = $this->currentTeacher($request)->ownedGuardians()->findOrFail($id);

        return view('guardians.create-child', compact('guardian'));
    }

    public function storeChild(Request $request, $id)
    {
        $teacher = $this->currentTeacher($request);
        $guardian = $teacher->ownedGuardians()->findOrFail($id);

        $data = $request->validate(FormRules::childProfile(), FormRules::messages());

        $data['address'] = FormRules::joinAddress($data);
        unset($data['barangay'], $data['municipality'], $data['province'], $data['region'], $data['photo']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('photos/children', 'local');
        }

        $data['is_studying'] = (bool) ($data['is_studying'] ?? false);
        if (! $data['is_studying']) {
            $data['school_name'] = null;
        }

        $assessmentDate = now()->toDateString();
        $child = DB::transaction(function () use ($guardian, $data, $teacher, $assessmentDate) {
            $child = $guardian->children()->create($data);
            ProgressRecord::create([
                'child_id' => $child->id,
                'teacher_id' => $teacher->id,
                'evaluation_date' => $assessmentDate,
                'evaluation_date_round_1' => $assessmentDate,
                'evaluation_number' => 1,
                'status' => 'pending',
            ]);

            return $child;
        });

        recordLog('created', 'Child', $child->id, 'Child created: '.$child->first_name.' '.$child->last_name);

        return redirect()->route('guardians.index')
            ->with('success', 'Child profile created successfully.');
    }

    public function unlinkChild(Request $request, $guardianId, $childId)
    {
        $teacher = $this->currentTeacher($request);
        $guardian = $teacher->ownedGuardians()->findOrFail($guardianId);
        $child = $teacher->ownedChildren()
            ->where('guardian_id', $guardian->id)
            ->findOrFail($childId);

        $child->delete();

        recordLog('deleted', 'Child', $child->id, 'Child archived: '.$child->first_name.' '.$child->last_name);

        $hasActiveChildren = $teacher->ownedChildren()->where('guardian_id', $guardian->id)->exists();
        $redirect = $hasActiveChildren
            ? redirect()->route('guardians.archive-child', $guardian->id)
            : redirect()->route('guardians.index');

        return $redirect->with('success', 'Child profile archived successfully.');
    }

    public function archiveChild(Request $request, $id)
    {
        $teacher = $this->currentTeacher($request);
        $guardian = $teacher->ownedGuardians()->findOrFail($id);
        $guardian->setRelation('children', $teacher->ownedChildren()
            ->where('guardian_id', $guardian->id)
            ->get());

        return view('guardians.archive-child', compact('guardian'));
    }

    private function currentTeacher(Request $request): Teacher
    {
        $teacher = $request->user()?->teacher;
        abort_unless($teacher, 403, 'A teacher profile is required.');

        return $teacher;
    }
}
