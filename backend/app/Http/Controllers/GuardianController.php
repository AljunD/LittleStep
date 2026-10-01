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

        recordLog('created', 'Guardian', $guardian->id, 'Guardian created: ' . $guardian->first_name . ' ' . $guardian->last_name);

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
        // Real-World Fix: Eager load the user relationship to prevent it evaluating to null during runtime
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

        // Strip address array inputs out before passing arrays into the data model updater
        unset($data['barangay'], $data['municipality'], $data['province'], $data['region']);

        // Save structural core database updates
        $guardian->update($data);

        // Update linked authentication tables 
        if ($guardian->user) {
            $newEmail = !empty($data['email']) ? $data['email'] : $guardian->user->email;
            $guardian->user->update(['email' => $newEmail]);

            if (!empty($data['password'])) {
                $guardian->user->update(['password' => bcrypt($data['password'])]);
            }
        }

        recordLog('updated', 'Guardian', $guardian->id, 'Guardian updated');

        // If requested via JavaScript fetch, return JSON so the success container appears!
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Guardian records updated successfully.'
            ]);
        }

        // Fallback for standard synchronous HTML form submittals
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

        recordLog('deleted', 'Guardian', $guardian->id, 'Guardian archived: ' . $guardian->first_name . ' ' . $guardian->last_name);

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
     * Show the archive child preview page dynamically.
     */
    public function archiveChild($id)
    {
        // Retrieve the guardian along with their currently active linked children
        $guardian = Guardian::with('children')->findOrFail($id);
        
        return view('guardians.archive-child', compact('guardian'));
    }
}