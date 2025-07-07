@php
    $itemLabels = [
        'Business Logo' => 'Business Logo',
        'Tax Document' => 'Tax Document',
        'Business Registration Document' => 'Business Registration Document',
        'Terms and Conditions Document' => 'Terms and Conditions Document',
    ];
@endphp

@if(isset($logo_path) && $logo_path)
    <div style="text-align:center; margin-bottom:20px;">
        <img src="{{ asset('storage/' . $logo_path) }}" alt="Business Logo" style="max-width:200px; max-height:200px;" />
        <p><strong>New Business Logo</strong></p>
    </div>
@endif

<p>Hello,</p>

<p>The details for your business <strong>{{ $business->name }}</strong> have been updated by an administrator ({{ $admin->name }} &lt;{{ $admin->email }}&gt;).</p>

@if(!empty($updatedItems))
    <p>The following items were updated:</p>
    <ul>
        @foreach($updatedItems as $item)
            <li>{{ $itemLabels[$item] ?? $item }}</li>
        @endforeach
    </ul>
@endif

@if(isset($tax_document_path) && $tax_document_path)
    <p><a href="{{ asset('storage/' . $tax_document_path) }}" download>Download new Tax Document</a></p>
@endif
@if(isset($registration_document_path) && $registration_document_path)
    <p><a href="{{ asset('storage/' . $registration_document_path) }}" download>Download new Registration Document</a></p>
@endif
@if(isset($terms_and_conditions_path) && $terms_and_conditions_path)
    <p><a href="{{ asset('storage/' . $terms_and_conditions_path) }}" download>Download new Terms and Conditions</a></p>
@endif

<p>If you did not request these changes or believe they were made in error, please contact the administrator at <a href="mailto:{{ $admin->email }}">{{ $admin->email }}</a> as soon as possible.</p>

<p>Thank you,<br>The Admin Team</p> 