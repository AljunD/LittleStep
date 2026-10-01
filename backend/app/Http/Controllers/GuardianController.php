<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guardian;

class GuardianController extends Controller
{
    /**
     * Define shared custom error messages for input form validation.
     */
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
            'email.email'                    => 'Please enter a valid email address.',
            'email.unique'                   => 'This email address is already registered to another user account.',
            'password.min'                   => 'The new password must be at least 8 characters long.',
            'password.confirmed'             => 'The password confirmation does not match your new password.',
            'barangay.max'                   => 'The barangay field must not exceed 255 characters.',
            'municipality.max'               => 'The municipality field must not exceed 255 characters.',
            'province.max'                   => 'The province field must not exceed 255 characters.',
            'region.max'                     => 'The region field must not exceed 255 characters.',
        ];
    }

    /**
     * Display a listing of guardians.
     */
    public function index()
    {
        $guardians = Guardian::with('children')->paginate(10);
        return view('guardians.index', compact('guardians'));
    }

    /**
     * Show the form for creating a new guardian.
     */
    public function create()
    {
        return view('guardians.create');
    }

    /**
     * Store a newly created guardian in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'            => ['required', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
            'middle_name'           => ['nullable', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
            'last_name'             => ['required', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
            'sex'                   => ['required', 'in:Male,Female'],
            'contact_number'        => ['required', 'regex:/^09[0-9]{9}$/'],
            'barangay'              => ['nullable', 'string', 'max:255'],
            'municipality'          => ['nullable', 'string', 'max:255'],
            'province'              => ['nullable', 'string', 'max:255'],
            'region'                => ['nullable', 'string', 'max:255'],
            'relationship_to_child' => ['required', 'regex:/^[A-Za-z\s]+$/', 'max:255'],
        ], $this->validationMessages());

        // Merge into one address string
        $data['address'] = implode(', ', array_filter([
            $data['barangay'] ?? null,
            $data['municipality'] ?? null,
            $data['province'] ?? null,
            $data['region'] ?? null,
        ]));

        $guardian = Guardian::create($data);

        if (function_exists('recordLog')) {
            recordLog('created', 'Guardian', $guardian->id, 'Guardian created: ' . $guardian->first_name . ' ' . $guardian->last_name);
        }

        return redirect()->route('guardians.index')
                         ->with('success', 'Guardian created successfully.');
    }

    /**
     * Display the specified guardian.
     */
    public function show($id)
    {
        $guardian = Guardian::with([
            'user' => fn($query) => $query->withTrashed(),
            'children'
        ])->findOrFail($id);

        return view('guardians.show', compact('guardian'));
    }

    /**
     * Show the form for editing the specified guardian.
     */
    public function edit($id)
    {
        $guardian = Guardian::findOrFail($id);

        // Split address back into parts cleanly even if commas are missing
        $parts = $guardian->address ? explode(',', $guardian->address) : [];
        $guardian->barangay     = isset($parts[0]) ? trim($parts[0]) : '';
        $guardian->municipality = isset($parts[1]) ? trim($parts[1]) : '';
        $guardian->province     = isset($parts[2]) ? trim($parts[2]) : '';
        $guardian->region       = isset($parts[3]) ? trim($parts[3]) : '';

        return view('guardians.edit', compact('guardian'));
    }

    /**
     * Update the specified guardian in storage.
     */
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

        // Preserve old values if fields are provided blank or empty
        foreach (['first_name', 'middle_name', 'last_name', 'sex', 'contact_number', 'relationship_to_child'] as $field) {
            $data[$field] = (!isset($data[$field]) || $data[$field] === '') ? $guardian->{$field} : $data[$field];
        }

        // Build address string from incoming parts dynamically
        $data['address'] = implode(', ', array_filter([
            $request->barangay,
            $request->municipality,
            $request->province,
            $request->region,
        ]));

        unset($data['barangay'], $data['municipality'], $data['province'], $data['region']);

        $guardian->update($data);

        // Update linked authentication tables 
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

    /**
     * Soft delete the specified guardian and linked user.
     */
    public function destroy($id)
    {
        $guardian = Guardian::findOrFail($id);
        $guardian->delete();

        if ($guardian->user) {
            $guardian->user->delete();
        }

        if (function_exists('recordLog')) {
            recordLog('deleted', 'Guardian', $guardian->id, 'Guardian archived: ' . $guardian->first_name . ' ' . $guardian->last_name);
        }

        return redirect()->route('guardians.index')
                         ->with('success', 'Guardian and linked user archived successfully.');
    }

    /**
     * Show the form for creating a child linked to the specified guardian.
     */
    public function createChild($id)
    {
        $guardian = Guardian::findOrFail($id);
        return view('guardians.create-child', compact('guardian'));
    }

    /**
     * Store a newly created child linked to the specified guardian.
     */
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

    /**
     * Show the archive child preview page dynamically.
     */
    public function archiveChild($id)
    {
        $guardian = Guardian::with('children')->findOrFail($id);
        
        return view('guardians.archive-child', compact('guardian'));
    }
}