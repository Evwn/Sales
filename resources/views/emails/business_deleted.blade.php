<h2>Your Business Has Been Deleted</h2>
<p>The following business and its branches have been deleted from the system:</p>
<ul>
    <li><strong>Business:</strong> {{ $businessName }}</li>
    <li><strong>Deleted Branches:</strong>
        <ul>
            @foreach($deletedBranches as $branch)
                <li>{{ $branch }}</li>
            @endforeach
        </ul>
    </li>
</ul>
<p><strong>Action performed by:</strong> {{ $admin->name }} ({{ $admin->email }})</p>
<p>If this was a mistake or you did not expect this action, please contact the support team immediately for recovery. All data will be lost if not recovered promptly.</p> 