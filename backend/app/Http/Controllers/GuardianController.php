<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guardian;
use App\Models\User;
use App\Models\Child;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GuardianController extends Controller
{
    protected function validationMessages()
    {
        return [
            'first_name.required'            => 'The first name field is required.',
            'first_name.regex'               => 'The first name must only contain letters and spaces.',
            'middle_name.regex'              => 'The middle name must only contain letters and spaces.',
            'last_name.required'             => 'The last name field is required.',
            'last_name.regex'                => 'The last name must only contain letters and spaces.',
            'sex.required'                   => 'Please select the guardian\'s sex.',
            'sex.in'                         => 'The selected sex must be either Male or Female.',
            'contact_number.required'        => 'The contact number field is required.',
            'contact_number.regex'           => 'The contact number must be exactly 11 digits starting with 09 (e.g., 09171234567).',
            'relationship_to_child.required' => 'The relationship to child field is required.',
            'relationship_to_child.regex'    => 'The relationship field must only contain letters and spaces.',
            'email.required'                 => 'The email address is required.',
            'email.email'                    => 'Please enter a valid email address.',
            'email.unique'                   => 'This email address is already registered to another user account.',
            'password.required'              => 'The password is required.',
            'password.min'                   => 'The new password must be at least 8 characters long.',
            'password.confirmed'             => 'The password confirmation does not match your new password.',
            'barangay.max'                   => 'The barangay field must not exceed 255 characters.',
            'municipality.max'               => 'The municipality field must not exceed 255 characters.',
            'province.max'                   => 'The province field must not exceed 255 characters.',
            'region.max'                     => 'The region field must not exceed 255 characters.',

            'child_first_name.required'      => 'The child\'s first name is required.',
            'child_last_name.required'       => 'The child\'s last name is required.',
            'child_sex.required'             => 'Please select the child\'s sex.',
            'child_date_of_birth.required'   => 'The child\'s date of birth is required.',
            'child_handedness.required'      => 'Please select child\'s handedness.',
            'photo.image'                    => 'The uploaded file must be an image.',
            'photo.max'                      => 'The photo size must not exceed 2MB.',
        ];
    }

    public function index()
    {
        $guardians = Guardian::with('children')->paginate(10);
        return view('guardians.index', compact('guardians'));
    }

    public function create()
    {
        return view('guardians.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['nullable', 'string', 'min:8', 'confirmed'],

            'first_name'            => ['required', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
            'middle_name'           => ['nullable', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
            'last_name'             => ['required', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
            'sex'                   => ['required', 'in:Male,Female'],
            'contact_number'        => ['required', 'regex:/^09[0-9]{9}$/'],
            'relationship_to_child' => ['required', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
            'barangay'              => ['nullable', 'string', 'max:255'],
            'municipality'          => ['nullable', 'string', 'max:255'],
            'province'              => ['nullable', 'string', 'max:255'],
            'region'                => ['nullable', 'string', 'max:255'],

            'child_first_name'      => ['required', 'string', 'max:255'],
            'child_middle_name'     => ['nullable', 'string', 'max:255'],
            'child_last_name'       => ['required', 'string', 'max:255'],
            'child_sex'             => ['required', 'in:Male,Female'],
            'child_date_of_birth'   => ['required', 'date'],
            'child_barangay'        => ['nullable', 'string', 'max:255'],
            'child_municipality'    => ['nullable', 'string', 'max:255'],
            'child_province'        => ['nullable', 'string', 'max:255'],
            'child_region'          => ['nullable', 'string', 'max:255'],
            'child_handedness'      => ['required', 'in:right,left,both,not_yet_established'],
            'is_studying'           => ['nullable'],
            'school_name'           => ['nullable', 'string', 'max:255'],
            'fathers_name'        => ['nullable', 'string', 'max:255'],
            'fathers_age'         => ['nullable', 'integer'],
            'fathers_occupation'  => ['nullable', 'string', 'max:255'],
            'fathers_education'   => ['nullable', 'string', 'max:255'],
            'mothers_name'        => ['nullable', 'string', 'max:255'],
            'mothers_age'         => ['nullable', 'integer'],
            'mothers_occupation'  => ['nullable', 'string', 'max:255'],
            'mothers_education'   => ['nullable', 'string', 'max:255'],
            'number_of_siblings'  => ['nullable', 'integer'],
            'birth_order'         => ['nullable', 'integer'],
            'photo'               => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ], $this->validationMessages());

        DB::transaction(function () use ($request, $validated) {
            $password = $validated['password'] ?? 'Password123!';

            $user = User::create([
                'email'    => $validated['email'],
                'password' => Hash::make($password),
                'role'     => 'guardian',
            ]);

            $guardianAddress = implode(', ', array_filter([
                $validated['barangay'] ?? null,
                $validated['municipality'] ?? null,
                $validated['province'] ?? null,
                $validated['region'] ?? null,
            ]));

            $guardian = Guardian::create([
                'user_id'               => $user->id,
                'first_name'            => $validated['first_name'],
                'middle_name'           => $validated['middle_name'] ?? null,
                'last_name'             => $validated['last_name'],
                'sex'                   => $validated['sex'],
                'contact_number'        => $validated['contact_number'],
                'address'               => $guardianAddress,
                'relationship_to_child' => $validated['relationship_to_child'],
            ]);

            $childAddress = implode(', ', array_filter([
                $validated['child_barangay'] ?? null,
                $validated['child_municipality'] ?? null,
                $validated['child_province'] ?? null,
                $validated['child_region'] ?? null,
            ]));

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('children_photos', 'public');
            }

            $guardian->children()->create([
                'first_name'         => $validated['child_first_name'],
                'middle_name'        => $validated['child_middle_name'] ?? null,
                'last_name'          => $validated['child_last_name'],
                'sex'                => $validated['child_sex'],
                'date_of_birth'      => $validated['child_date_of_birth'],
                'address'            => $childAddress,
                'handedness'         => $validated['child_handedness'],
                'is_studying'        => $request->has('is_studying') && $request->is_studying == '1',
                'school_name'        => $validated['school_name'] ?? null,
                'fathers_name'       => $validated['fathers_name'] ?? null,
                'fathers_age'        => $validated['fathers_age'] ?? null,
                'fathers_occupation' => $validated['fathers_occupation'] ?? null,
                'fathers_education'  => $validated['fathers_education'] ?? null,
                'mothers_name'       => $validated['mothers_name'] ?? null,
                'mothers_age'        => $validated['mothers_age'] ?? null,
                'mothers_occupation' => $validated['mothers_occupation'] ?? null,
                'mothers_education'  => $validated['mothers_education'] ?? null,
                'number_of_siblings' => $validated['number_of_siblings'] ?? null,
                'birth_order'        => $validated['birth_order'] ?? null,
                'photo_path'         => $photoPath,
            ]);

            if (function_exists('recordLog')) {
                recordLog('created', 'Guardian', $guardian->id, 'Guardian and child registered: ' . $guardian->first_name . ' ' . $guardian->last_name);
            }
        });

        return redirect()->route('guardians.create')->with('registration_success', true);
    }

    public function show($id)
    {
        $guardian = Guardian::with([
            'user' => fn($query) => $query->withTrashed(),
            'children'
        ])->findOrFail($id);

        return view('guardians.show', compact('guardian'));
    }

    public function edit($id)
    {
        $guardian = Guardian::findOrFail($id);

        $parts = $guardian->address ? explode(',', $guardian->address) : [];
        $guardian->barangay     = isset($parts[0]) ? trim($parts[0]) : '';
        $guardian->municipality = isset($parts[1]) ? trim($parts[1]) : '';
        $guardian->province     = isset($parts[2]) ? trim($parts[2]) : '';
        $guardian->region       = isset($parts[3]) ? trim($parts[3]) : '';

        return view('guardians.edit', compact('guardian'));
    }

    public function update(Request $request, $id)
    {
        $guardian = Guardian::with('user')->findOrFail($id);

        $userId = $guardian->user ? $guardian->user->id : 'NULL';

        $data = $request->validate([
            'first_name'            => ['nullable', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
            'middle_name'           => ['nullable', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
            'last_name'             => ['nullable', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
            'sex'                   => ['nullable', 'in:Male,Female'],
            'contact_number'        => ['nullable', 'regex:/^09[0-9]{9}$/'],
            'barangay'              => ['nullable', 'string', 'max:255'],
            'municipality'          => ['nullable', 'string', 'max:255'],
            'province'              => ['nullable', 'string', 'max:255'],
            'region'                => ['nullable', 'string', 'max:255'],
            'relationship_to_child' => ['nullable', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
            'email'                 => ['nullable', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password'              => ['nullable', 'string', 'min:8', 'confirmed'],
        ], $this->validationMessages());

        foreach (['first_name', 'middle_name', 'last_name', 'sex', 'contact_number', 'relationship_to_child'] as $field) {
            $data[$field] = (!isset($data[$field]) || $data[$field] === '') ? $guardian->{$field} : $data[$field];
        }

        $data['address'] = implode(', ', array_filter([
            $request->barangay,
            $request->municipality,
            $request->province,
            $request->region,
        ]));

        unset($data['barangay'], $data['municipality'], $data['province'], $data['region']);

        $guardian->update($data);

        if ($guardian->user) {
            $newEmail = !empty($data['email']) ? $data['email'] : $guardian->user->email;
            $guardian->user->update(['email' => $newEmail]);

            if (!empty($data['password'])) {
                $guardian->user->update(['password' => bcrypt($data['password'])]);
            }
        }

        if (function_exists('recordLog')) {
            recordLog('updated', 'Guardian', $guardian->id, 'Guardian updated');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Guardian records updated successfully.'
            ]);
        }

        return redirect()->route('guardians.index')
                         ->with('success', 'Guardian updated successfully.');
    }

    public function destroy($id)
    {
        $guardian = Guardian::with('children')->findOrFail($id);

        foreach ($guardian->children as $child) {
            $child->delete();

            if (function_exists('recordLog')) {
                recordLog('deleted', 'Child', $child->id, 'Child archived with Guardian: ' . $child->first_name . ' ' . $child->last_name);
            }
        }

        $guardian->delete();

        if ($guardian->user) {
            $guardian->user->delete();
        }

        if (function_exists('recordLog')) {
            recordLog('deleted', 'Guardian', $guardian->id, 'Guardian archived: ' . $guardian->first_name . ' ' . $guardian->last_name);
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Guardian and all linked children archived successfully.'
            ]);
        }

        return redirect()->route('guardians.index')
                        ->with('success', 'Guardian and all linked children archived successfully.');
    }

    public function createChild($id)
    {
        $guardian = Guardian::findOrFail($id);
        return view('guardians.create-child', compact('guardian'));
    }

    public function storeChild(Request $request, $id)
    {
        $guardian = Guardian::findOrFail($id);

        $data = $request->validate([
            'first_name'         => ['required', 'string', 'max:255'],
            'middle_name'        => ['nullable', 'string', 'max:255'],
            'last_name'          => ['required', 'string', 'max:255'],
            'sex'                => ['required', 'in:Male,Female'],
            'date_of_birth'      => ['required', 'date'],
            'barangay'           => ['nullable', 'string', 'max:255'],
            'municipality'       => ['nullable', 'string', 'max:255'],
            'province'           => ['nullable', 'string', 'max:255'],
            'region'             => ['nullable', 'string', 'max:255'],
            'handedness'         => ['required', 'in:right,left,both,not_yet_established'],
            'is_studying'        => ['nullable', 'boolean'],
            'school_name'        => ['nullable', 'string', 'max:255'],
            'fathers_name'       => ['nullable', 'string', 'max:255'],
            'fathers_age'        => ['nullable', 'integer'],
            'fathers_occupation' => ['nullable', 'string', 'max:255'],
            'fathers_education'  => ['nullable', 'string', 'max:255'],
            'mothers_name'       => ['nullable', 'string', 'max:255'],
            'mothers_age'        => ['nullable', 'integer'],
            'mothers_occupation' => ['nullable', 'string', 'max:255'],
            'mothers_education'  => ['nullable', 'string', 'max:255'],
            'number_of_siblings' => ['nullable', 'integer'],
            'birth_order'        => ['nullable', 'integer'],
            'photo'              => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $data['address'] = implode(', ', array_filter([
            $request->barangay,
            $request->municipality,
            $request->province,
            $request->region,
        ]));

        unset($data['barangay'], $data['municipality'], $data['province'], $data['region']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('children_photos', 'public');
        }

        $data['is_studying'] = (bool) ($request->is_studying ?? false);

        $child = $guardian->children()->create($data);

        if (function_exists('recordLog')) {
            recordLog('created', 'Child', $child->id, 'Child created: ' . $child->first_name . ' ' . $child->last_name);
        }

        return redirect()->route('guardians.index')
                         ->with('success', 'Child profile created successfully.');
    }

    public function unlinkChild($guardianId, $childId)
    {
        $guardian = Guardian::findOrFail($guardianId);
        $child = $guardian->children()->findOrFail($childId);

        $child->delete();

        if (function_exists('recordLog')) {
            recordLog('deleted', 'Child', $child->id, 'Child archived: ' . $child->first_name . ' ' . $child->last_name);
        }

        return response()->json([
            'success' => true,
            'message' => 'Child profile archived successfully.'
        ]);
    }

    public function archiveChild($id)
    {
        $guardian = Guardian::with('children')->findOrFail($id);
        
        return view('guardians.archive-child', compact('guardian'));
    }
}