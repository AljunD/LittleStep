<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your LittleStep Account Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="max-w-lg mx-auto bg-white shadow-md rounded-lg p-6 mt-10">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Your KidWatch2 Account Has Been Verified!</h2>
        <p class="text-gray-700 mb-4">Here are your account details:</p>
        <ul class="space-y-2 text-gray-800">
            <li><strong>Name:</strong> {{ $user->teacher->first_name }} {{ $user->teacher->middle_name }} {{ $user->teacher->last_name }}</li>
            <li><strong>Contact Number:</strong> {{ $user->teacher->contact_number }}</li>
            <li><strong>Address:</strong> {{ $user->teacher->address }}</li>
            <li><strong>Email:</strong> {{ $user->email }}</li>
            <li><strong>Password:</strong> (Use the password you set during registration)</li>
        </ul>
        <p class="mt-6 text-gray-700">You can now log in and access your dashboard.</p>
        <div class="mt-6">
            <a href="{{ route('dashboard') }}"
               class="inline-block bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-black transition">
               Go to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
