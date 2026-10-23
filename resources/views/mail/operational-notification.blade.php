{{-- path: resources/views/mail/operational-notification.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $headline }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #172033;">
    <h1 style="font-size: 20px;">
        {{ $headline }}
    </h1>

    <p>
        A new operational event was recorded in the CIM CMS.
    </p>

    <table
        cellpadding="8"
        cellspacing="0"
        border="1"
        style="border-collapse: collapse; border-color: #dbe1ea;"
    >
        @foreach ($details as $label => $value)
            <tr>
                <th align="left">{{ $label }}</th>
                <td>{{ $value }}</td>
            </tr>
        @endforeach
    </table>

    <p>
        Sign in to the CMS using your authorized account to review the record.
    </p>

    <p style="font-size: 12px; color: #64748b;">
        Notification reference: {{ $outboxId }}
    </p>
</body>
</html>
