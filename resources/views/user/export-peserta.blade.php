<table>
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>No Whatsapp</th>
        <th>Email</th>
        <th>Role</th>
        <th>Nama Komunitas</th>
        <th>Provinsi</th>
        <th>Kabupaten</th>
        <th>Kecamatan</th>
        <th>Desa</th>
        <th>Alamat Lengkap</th>
        <th>Username Instagram</th>
        <th>Tanggal Lahir</th>
        <th>Jumlah Film</th>
    </tr>
    @foreach($users as $index => $user)
    <tr>
        <td>{{ $index + 1 }}</td>
        <td>{{ $user->name }}</td>
        <td>{{ $user->no_hp }}</td>
        <td>{{ $user->email }}</td>
        <td>{{ strtoupper($user->role) }}</td>
        <td>{{ $user->detail->community_name ?? '-' }}</td>
        <td>{{ $user->detail->provinsi_name ?? '-' }}</td>
        <td>{{ $user->detail->kabupaten_name ?? '-' }}</td>
        <td>{{ $user->detail->kecamatan_name ?? '-' }}</td>
        <td>{{ $user->detail->desa_name ?? '-' }}</td>
        <td>{{ $user->detail->alamat_lengkap ?? '-' }}</td>
        <td>{{ $user->detail->username_ig ?? '-' }}</td>
        <td>{{ optional($user->detail->tanggal_lahir ?? null)->format('d M Y') ?? '-' }}</td>
        <td>{{ $user->films_count }}</td>
    </tr>
    @endforeach
</table>
