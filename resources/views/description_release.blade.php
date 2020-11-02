@extends('layout')

@section('title', 'Descrição de Lançamentos')

@section('css')

<!-- DataTables -->
<link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css')}}">
 
<!-- Select2 -->
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">

@endsection

@section('scripts')
<!-- DataTables  & Plugins -->
<script src="{{asset('plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js')}}"></script>
<script src="{{asset('plugins/jszip/jszip.min.js')}}"></script>
<script src="{{asset('plugins/pdfmake/pdfmake.min.js')}}"></script>
<script src="{{asset('plugins/pdfmake/vfs_fonts.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/buttons.html5.min.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/buttons.print.min.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/buttons.colVis.min.js')}}"></script>

<!-- Select2 -->
<script src="{{asset('plugins/select2/js/select2.full.min.js')}}"></script>

<script>
$(document).ready(function() {
    //inicializando select2
    //$('.select2').select2();
    $('.select2').select2({
      theme: 'bootstrap4'
    });
    //validando token
    $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
    });

    //inicio - data table config
    var table = $('#descriptions').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: '{!! route('descriptions.data') !!}',
        language: {
        "url": "{{ asset('plugins/DataTables/portugues.json') }}"
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'type', name: 'type' },
            { data: 'description', name: 'description' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    }); 

    //fim - data table config

    //inicio - criar novo registro
    $('#createNewItem').click(function () {
        $(".print-error-msg").find("ul").html('');
        $(".print-error-msg").css('display','none');
        $('#saveBtn').val("create-item");
        $('#item_id').val('');
        $('#ItemForm').trigger("reset");
        $('#modelHeading').html("Nova Descrição de Lançamento");
        $('#ajaxModel').modal('show');
    });
    //fim - criar novo registro
    
    //inicio - editar registro
    $('body').on('click', '.editItem', function () {
      var item_id = $(this).data('id');
      $.get("{{ route('descriptions.index') }}" +'/' + item_id +'/edit', function (data) {
            $(".print-error-msg").find("ul").html('');
            $(".print-error-msg").css('display','none');
            $('#modelHeading').html("Editar Descrição de Lançamento");
            $('#saveBtn').val("edit-description");
            $('#ajaxModel').modal('show');
            $('#item_id').val(data.id); //campos
            if(data.type == 1){
                $("#type1").prop("checked", true);
            }else{
                $("#type2").prop("checked", true);
            }
            $('#description').val(data.description); //campos
            console.log(data);
      })
   });
   //fim - editar registro

   //inicio - store registro
   $('#saveBtn').click(function (e) {
        e.preventDefault();
        
        //get inputs
        var _token = $("input[name='_token']").val();
        var type = $("input[name='type']").val();
        var description = $("input[name='description']").val();
    
        $.ajax({
            data: $('#ItemForm').serialize(),
            //data: {_token:_token, type:type, description:description},
            url: "{{ route('descriptions.store') }}",
            type: "POST",
            dataType: 'json',
            success: function (data) {
                //alert(data.success);
                //console.log('Success:', data.success);
                //console.log('Error:', data.error);

                if($.isEmptyObject(data.error)){
                    //alert(data.success);

                    Swal.fire({
                        icon: 'success',
                        title: data.success,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    $('.select2').val(null).trigger('change');
                    $('#ItemForm').trigger("reset");
                    $('#ajaxModel').modal('hide');
                    $('#saveBtn').html('Salvar');
                    table.draw();

                }else{
                    printErrorMsg(data.error);
                    $('#saveBtn').html('Salvar');
                }
            }   //,
                //error: function (data) {
                //    console.log('Error:', data);
                //    $('#saveBtn').html('Salvar');
                //}
        });

        function printErrorMsg (msg) {
            $(".print-error-msg").find("ul").html('');
            $(".print-error-msg").css('display','block');
            $.each( msg, function( key, value ) {
                $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
            });
        }

    });
    //fim - store registro

    //inicio - delete registro
    $('body').on('click', '.deleteItem', function () {
     
        var item_id = $(this).data("id");

        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
        });
        //confirm("Are You sure want to delete !");

        swalWithBootstrapButtons.fire({
        title: 'Você tem certeza?',
        text: "Você não poderá reverter isso!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Não, cancelar!',
        reverseButtons: true
        }).then((result) => {
        if (result.isConfirmed) {

            $.ajax({
            type: "DELETE",
            url: "{{ route('descriptions.store') }}"+'/'+item_id,
            success: function (data) {
                table.draw();
            },
            error: function (data) {
                console.log('Error:', data);
            }
            }),

            swalWithBootstrapButtons.fire(
            'Excluido!',
            'Seu registro foi excluído.',
            'success'
            )


        } else if (
            /* Read more about handling dismissals below */
            result.dismiss === Swal.DismissReason.cancel
        ) {
            swalWithBootstrapButtons.fire(
            'Cancelado',
            'Seu registro está seguro:)',
            'error'
            )
        }
        })
    });
    //fim - delete registro

});

</script>
    
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
        <div class="card-header">
        <h3 class="card-title"><a href="javascript:void(0)" id="createNewItem" class="btn btn-block btn-primary"><i class="fas fa-plus"></i> Novo</a></h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <table id="descriptions" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="5%" >#</th>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Criado em</th>
                        <th width="15%">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
</div>
<!-- .modal -->
<div class="modal fade" id="ajaxModel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modelHeading"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger print-error-msg" style="display:none">
                    <ul></ul>
                </div>
                <form id="ItemForm" name="ItemForm" class="form-horizontal">
                   <input type="hidden" name="item_id" id="item_id">
                    <div class="form-group">
                        <label for="type" class="col-sm-2 control-label">Tipo<span class="text-danger">*</span></label>
                        <div class="col-sm-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input type" type="radio" id="type1" name="type" value="1">
                                <label class="form-check-label">1 - Entrada</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input type" type="radio" id="type2" name="type" value="2">
                                <label class="form-check-label">2 - Saída</label>
                            </div>
                            
                            {{-- <select name="type" id="type" class="form-control select2">
                                <option selected="selected" value="">Selecione uma opção</option>
                                <option value="1">1 - Entrada</option>
                                <option value="2">2 - Saída</option>
                            </select> --}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description" class="col-sm-3 control-label">Descrição<span class="text-danger">*</span></label>
                        <div class="col-sm-12">
                            <input id="description" name="description" placeholder="Digite a descrição.." class="form-control">
                        </div>
                    </div>
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-success" id="saveBtn" value="create">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /.modal -->

@endsection