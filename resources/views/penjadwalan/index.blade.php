@extends('layouts.app')
@section('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('wrapper')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Data Penjadwalan</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item active" aria-current="page">Tabel Penjadwalan</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <button class="btn btn-primary btn-sm add"><i class="fa-solid fa-plus me-1"></i>Tambah Data
                        Penjadwalan</button>
                </div>
                <div class="card-body">
                    @if (in_array(session('role'), [1]))
                        <div class="form group mb-3">
                            <label for="prodi-f" class="col-form-label">Program Studi</label>
                            <select id="prodi-f" class="form-control w-25"></select>
                        </div>
                    @endif
                    <table class="table table-striped" id="table">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Matakuliah</th>
                                <th>Periode</th>
                                <th>Semester</th>
                                <th>Prodi</th>
                                <th>Kelas</th>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>SKS</th>
                                <th>Gedung</th>
                                <th>Ruang</th>
                                <th>Dosen</th>
                                <th>Act</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('penjadwalan.create')
    @include('penjadwalan.update')
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(function() {
            var prodi = '{{ App\helpers\infoUser()->id_prodi ?? 0 }}'
            getProdi()
            if (prodi == 0) {
                $('#id_prodi,#e-id_prodi').attr('readonly', false)
            } else {
                $('#id_prodi,#e-id_prodi').attr('readonly', true)
                $('.view-prodi,.e-view-prodi').css('height', '10vh');
            }
            $("#jam_mulai,#e-jam_mulai").flatpickr({
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                onChange: function(selectedDates, dateStr, instance) {
                    // Saat jam mulai berubah, atur opsi minDate untuk jam selesai
                    if (dateStr) {
                        var minTime = instance.parseDate(dateStr, "H:i");
                        $("#jam_selesai,#e-jam_selesai").flatpickr({
                            enableTime: true,
                            noCalendar: true,
                            dateFormat: "H:i",
                            time_24hr: true,
                            minDate: minTime
                        });
                    }
                }
            });

            $("#jam_selesai,#e-jam_selesai").flatpickr({
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true
            });
            var idData = null
            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                ajax: {
                    url: '{!! route('trx.penjadwalan.index') !!}',
                    type: "GET",
                    data: function(data) {
                        data.prodi = prodi == 0 ? $('#prodi-f').val() : prodi
                    },
                    dataSrc: "data",
                },
                columns: [{
                        data: 'id',
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'nama_matkul',

                    },
                    {
                        data: 'periode'
                    },
                    {
                        data: 'semester'
                    },
                    {
                        data: 'data_prodi',
                        render: function(data) {
                            let list = ''
                            data.forEach(element => {
                                list += `<li>${element.nama_prodi}</li>`
                            });
                            return list
                        }
                    },
                    {
                        data: 'rombel'
                    },
                    {
                        data: 'hari'
                    },
                    {
                        data: 'jam'
                    },
                    {
                        data: 'sks'
                    },
                    {
                        data: 'data_gedung'
                    },
                    {
                        data: 'nama_kelas'
                    },
                    {
                        data: 'nama_dosen'
                    },
                    {
                        data: 'id',
                        render: function(data) {
                            return `<div class="d-flex flex-row">
                                            <button type="button" class="btn btn-outline-warning btn-sm px-2 me-1 edit"
                                                fdprocessedid="2ybyt"><i
                                                    class="fa-solid fa-pen-to-square mr-1"></i>edit</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm px-2 ms-1 hapus"
                                                fdprocessedid="2ybyt"><i class="fa-solid fa-trash mr-1"></i>Hapus</button>
                                        </div>`
                        }
                    }

                ],
                order: [
                    [9, 'asc']
                ],
                columnDefs: [{
                    visible: false,
                    targets: 6
                }, {
                    visible: false,
                    targets: 9
                }],
                rowGroup: {
                    dataSrc: ['data_gedung', 'hari']
                }
            });

            $('#prodi-f').change(function(e) {
                table.ajax.reload()

            });

            $('.add').on('click', function() {
                $('#id_prodi').val('')
                $('#nama_matkul').val('')
                $('#id_semester').val('')
                $('#sks').val('')
                getProdi()
                $('#add-form').modal('show')
            })

            table.on('click', '.edit', function() {
                let data = table.row($(this).parents('tr')).data()
                idData = data.id
                let prodi = []
                let jam = data.jam.split('-')
                let matkul = new Option(data.nama_matkul, data.id_matkul, true, true);
                let ruang = new Option(data.nama_kelas, data.id_kelas, true, true);
                let dosen = new Option(data.nama_dosen, data.id_dosen, true, true);
                $('#e-hari').val(data.hari)
                $('#e-rombel').val(data.rombel)
                $('#e-jam_mulai').val(jam[0])
                $('#e-jam_selesai').val(jam[1])
                $('#e-id_matkul').append(matkul).trigger('change');
                $('#e-id_kelas').append(ruang).trigger('change');
                $('#e-id_dosen').append(dosen).trigger('change');

                data.data_prodi.forEach(element => {
                    prodi.push({
                        id: element.id
                    })
                });
                getProdi(prodi)
                // getSemester(data.semester)
                $('#edit-form').modal('show')
            });

            $('form#form-add').submit(function(e) {
                e.preventDefault();
                e.stopPropagation()

                let formDataArray = new FormData(this)
                const url = '{{ route('trx.penjadwalan.store') }}';
                const requestOptions = {
                    method: 'POST',
                    body: formDataArray // Ubah objek ke dalam format JSON sebelum mengirim
                };

                fetch(url, requestOptions)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        $('#add-form').modal('hide')
                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: 'Your work has been saved',
                            showConfirmButton: false,
                            timer: 1500
                        }).then((result) => {
                            table.ajax.reload()
                        })

                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Ruangan sudah diisi pada jadwal yang sama',
                        });
                    });

            });

            $('form#form-edit').submit(function(e) {
                e.preventDefault();
                e.stopPropagation()
                let formDataArray = new FormData(this)
                const url = '{{ route('trx.penjadwalan.update', ['penjadwalan' => ':idData']) }}'.replace(
                    ':idData',
                    idData);
                const requestOptions = {
                    method: 'POST',
                    body: formDataArray // Ubah objek ke dalam format JSON sebelum mengirim
                };

                fetch(url, requestOptions)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log(data)
                        $('#edit-form').modal('hide')
                        Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: "Your work has been saved",
                            showConfirmButton: false,
                            timer: 1500
                        }).then((result) => {
                            idData = null
                            table.ajax.reload();
                        })
                    })
                    .catch(error => {
                        console.error(error);
                    });

            });



            table.on('click', '.hapus', function() {
                let data = table.row($(this).parents('tr')).data()
                idData = data.id
                Swal.fire({
                    title: "Are you sure?",
                    text: "Data Akan Dihapus!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteData(idData)
                    }
                });
            });

            function deleteData(id) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content');

                const url = '{{ route('trx.penjadwalan.destroy', ['penjadwalan' => ':id']) }}'.replace(':id',
                    idData);
                const requestOptions = {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken // sertakan token CSRF di sini
                    }
                };

                fetch(url, requestOptions)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: "Your work has been Delete",
                            showConfirmButton: false,
                            timer: 1500
                        }).then((result) => {
                            idData = null
                            table.ajax.reload();
                        })
                    })
                    .catch(error => {
                        console.error('Error deleting data:', error);
                    });
            }
            getMatkul()
            getKelas()
            getDosen()

            function getProdi(datas = null) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content');
                let gets = ''
                if(prodi != 0){
                    gets = '&prodi='+prodi
                }

                fetch('{{ route('master.prodi.index') }}?ajax=true'+gets, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (prodi == 0) {
                            let options = '<option value="all">Pilih Program Study</option>';
                            data.data.forEach(item => {
                                options +=
                                    `<option value="${item.id}">${item.jenjang_study}-${item.nama_prodi}</option>`;
                            });
                            $('#prodi-f').html(options);
                        }


                        var dataLength = 50;
                        var container = datas == null ? $('.view-prodi') : $('.e-view-prodi');
                        container.empty()
                        var columnDiv = $('<div class="col"></div>');
                        var count = 0;

                        $.each(data.data, function(index, item) {
                            let selected
                            if (datas != null) {
                                const result = datas.find(items => items.id === item.id);
                                if (result) {
                                    selected = 'checked';
                                }
                            }
                            var checkboxDiv = $('<div class="form-check"></div>');
                            var checkbox = $(
                                '<input class="form-check-input" type="checkbox" id="inlineCheckbox' +
                                item.id + '" value="' + item.id + '" name="prodi_id[]" ' +
                                selected + '>');
                            var label = $('<label class="form-check-label" for="inlineCheckbox' + item
                                .id + '">' + item.nama_prodi + '</label>');

                            checkboxDiv.append(checkbox);
                            checkboxDiv.append(label);
                            columnDiv.append(checkboxDiv);

                            count++;

                            if (count % 25 == 0 || (count == dataLength && count < 10)) {
                                container.append(columnDiv);
                                columnDiv = $('<div class="col"></div>');
                            }
                        });


                        container.append(columnDiv);

                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            }

            function getSemester(datas = null) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content');

                fetch('{{ route('master.semester.index') }}?ajax=true', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok.');
                        }
                        return response.json();
                    })
                    .then(data => {

                        var container = $('.view-semester');
                        container.empty()
                        data.data.forEach(function(item, index) {
                            let selectd
                            if (datas != null && datas == item.id) {
                                selectd = 'checked'
                            }
                            var radioDiv = $('<div class="form-check form-check-inline"></div>');
                            var radioInput = $(
                                '<input class="form-check-input" type="radio" name="semester" id="inlineRadio' +
                                item.id + '" value="' + item.id + '" required ' + selectd + '>');
                            var radioLabel = $('<label class="form-check-label" for="inlineRadio' + item
                                .id + '">' + item.semester + '</label>');

                            radioDiv.append(radioInput);
                            radioDiv.append(radioLabel);
                            container.append(radioDiv);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            }

            function getMatkul() {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content');

                fetch('{{ route('master.matakuliah.index') }}?ajax=true', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        let opsi = []
                        data.data.forEach(element => {
                            opsi.push({
                                id: element.id,
                                text: element.nama_matkul
                            })
                        });

                        $("#single-select-field,#e-id_matkul").select2({
                            theme: "bootstrap-5",
                            data: opsi
                        });

                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            }

            function getKelas() {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content');
                const url = '{{ route('master.kelas.getgrouped', ['group' => ':idData']) }}'.replace(
                    ':idData', 'all');
                fetch(url, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        $("#id_kelas,#e-id_kelas").select2({
                            theme: "bootstrap-5",
                            data: data,
                            templateResult: formatData,
                            templateSelection: formatData
                        });

                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            }

            function formatData(data) {
                if (!data.id) {
                    return $('<strong>' + data.text + '</strong>');
                }
                var $state = $('<span>' + data.text + '</span>');
                return $state;
            }

            function getDosen() {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content');

                fetch('{{ route('master.dosen.index') }}?ajax=true', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        let opsi = []
                        data.data.forEach(element => {
                            opsi.push({
                                id: element.id,
                                text: element.nama_dosen
                            })
                        });

                        $("#id_dosen,#e-id_dosen").select2({
                            theme: "bootstrap-5",
                            data: opsi
                        });

                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            }

        });
    </script>
@endsection
