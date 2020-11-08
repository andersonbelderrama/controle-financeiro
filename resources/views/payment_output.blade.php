@extends('layout')

@section('title', 'Pagamentos')

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

<!-- InputMask -->
<script src="{{asset('plugins/moment/moment.min.js')}}"></script>
<script src="{{asset('plugins/inputmask/jquery.inputmask.min.js')}}"></script>

<!-- Jquery.Mask -->
<script src="{{asset('plugins/jquery-maskmoney/jquery.maskMoney.min.js')}}"></script>

<script>
$(document).ready(function() {

    $.ajaxSetup({
       headers:{
           'X_CSRF_TOKEN': $('meta[name="csrf-token"]').attr('content')
       }
    });

    //condição do filtro & toggle
    $('#filter-date').click(function(){
    $('.filter-date').slideToggle();
    });
    $('#filter-clear').click(function(){
        $('#coluna').val("0");
        $('#data_inicial').val("");
        $('#data_inicial').attr('disabled','disabled');
        $('#data_final').val("");
        $('#data_final').attr('disabled','disabled');
        table.draw(true);
    });
    $('#coluna').click(function(){
        if ($("#coluna").val() != '0') {
            $('#data_inicial').removeAttr('disabled');
            $('#data_final').removeAttr('disabled');
        }else{
            $('#data_inicial').attr('disabled','disabled');
            $('#data_final').attr('disabled','disabled');
        }
    });

    //date picker
    $('.date-picker').datepicker({ 
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
        dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
        dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
        monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
        monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior'
    });

    $('#filter-submit').click(function(){
        table.draw(true);
    });

    //inicio - config inputs
    $('.select2').select2({
    theme: 'bootstrap4'
    });

    $("#amount").maskMoney({prefix:'R$ ', allowNegative: true, thousands:'.', decimal:',', affixesStay: true}); 

    $('#payment_date').inputmask({ alias: "datetime", inputFormat: "dd/mm/yyyy", placeholder: "DD/MM/AAAA"});
    $('#due_date').inputmask({ alias: "datetime", inputFormat: "dd/mm/yyyy", placeholder: "DD/MM/AAAA"});
    
    /* clean validations */
    function cleanValidations(){
        $('.error').hide().html(''); /* hide span.error */ 
        $('.form-control').removeClass( "is-invalid" ); /* remove red border class */
        $('.alert-success').hide().html(''); /* hide span.success */
    }
    //fim - config inputs

    //inicio - data table config
    var table = $('#payment_outputs').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route('payment_outputs.data') }}',
            type: 'GET',
            data: function (d) {
            d.coluna = $('#coluna').val();
            d.inicial = $('#data_inicial').val();
            d.final = $('#data_final').val();
            }
        },
        language: {
        "url": "{{ asset('plugins/DataTables/portugues.json') }}"
        },
        "order": [[ 0, "desc" ]],
        columns: [
            { data: 'id', name: 'id' },
            { data: 'description', name: 'description' },
            { data: 'extra_info', name: 'extra_info' },
            { data: 'amount', name: 'amount' },
            { data: 'due_date', name: 'due_date' },
            { data: 'payment_date', name: 'payment_date' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    //inicio - criar novo registro
    $('#createNewItem').click(function () {
        cleanValidations();
        $('.select2').val(null).trigger('change');
        $('#saveBtn').val("create-item");
        $('#item_id').val('');
        $('#ItemForm').trigger("reset");
        $('#modelHeading').html("Adicionar Novo Pagamento");
        $('#ajaxModel').modal('show');
    });
    //fim - criar novo registro

    //inicio - editar registro
    $('body').on('click', '.editItem', function () {
        var item_id = $(this).data('id');

        $.get("{{ route('payment_outputs.index') }}" +'/' + item_id +'/edit', function (data) {
            cleanValidations();
            $('#modelHeading').html("Editar Pagamento");
            $('#saveBtn').val("edit-input");
            $('#ajaxModel').modal('show');
            $('#item_id').val(data.id); //campos
            $(".select2").val(data.description_id).trigger('change'); 
            $('#extra_info').val(data.extra_info);
            $('#amount').val(data.amount); //campos
            $('#due_date').val(data.due_date); //campo
            $('#payment_date').val(data.payment_date); //campos  
        })
    });
   //fim - editar registro

   //inicio - store registro
   $('#saveBtn').click(function (e) {
        e.preventDefault();
        cleanValidations();
        var dados = $('#ItemForm').serialize();
    
        $.ajax({
            data: dados,
            url: "{{ route('payment_outputs.store') }}",
            type: "POST",
            dataType: 'json',
            success: function (response) {
                if(response.errors == null){ 
                    Swal.fire({
                        icon: 'success',
                        title: response.success,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    $('.select2').val(null).trigger('change');
                    $('#ItemForm').trigger("reset");
                    $('#ajaxModel').modal('hide');
                    $('#saveBtn').html('Salvar');
                    table.draw();

                }else{
                    $.each(response.errors, function(key, value){
                        $('#'+key).addClass( "is-invalid" );
                        $('#'+key+'-error').show().append(value);
                    });
                }
            },
        });
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
            url: "{{ route('payment_outputs.store') }}"+'/'+item_id,
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
            <a href="javascript:void(0)" id="createNewItem" class="btn btn-primary d-inline"><i class="fas fa-plus"></i> Novo</a>

            <a href="javascript:void(0)" id="filter-date" class="btn btn-default d-inline"><i class="fas fa-calendar-alt"></i> Filtrar por data</a>
        </div>
        <!-- /.card-header -->
        <div class="card-header filter-date" style="display: none;">
            <div class="row form-group">
                <div class="col-sm-2">
                    <select id="coluna" name="coluna" class="form-control">
                        <option value="0" selected>Escolha uma opção..</option>
                        <option value="1">Vencimento</option>
                        <option value="2">Pagamento</option>
                    </select>
                </div>
                <div class="col-sm-2">
                    <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text">
                            <i class="far fa-calendar-alt"></i>
                          </span>
                        </div>
                        <input type="text" class="form-control float-right date-picker" disabled id="data_inicial" value="" name="data_inicial" placeholder="Data inicial..">
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text">
                            <i class="far fa-calendar-alt"></i>
                          </span>
                        </div>
                        <input type="text" class="form-control float-right date-picker" disabled id="data_final" name="data_final" placeholder="Data final..">
                    </div>
                </div>
                <div class="col-sm-2">
                    <button type="text" id="filter-submit" class="btn btn-default d-inline"><i class="fas fa-search"></i> Filtrar</button>
                    <button type="text" id="filter-clear" class="btn btn-danger d-inline"><i class="fas fa-eraser"></i> Limpar</button>
                </div> 
            </div>
        </div>
        <div class="card-body">
            <table id="payment_outputs" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="5%" >#</th>
                        <th>Descrição</th>
                        <th>Comentário</th>
                        <th>Valor</th>
                        <th>Vencimento</th>
                        <th>Pagamento</th>
                        <th width="15%">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
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
                <form id="ItemForm" name="ItemForm" class="form-horizontal">
                    @csrf
                   <input type="hidden" name="item_id" id="item_id">
                    <div class="form-group">
                        <label for="description_id" class="col-sm-2 control-label">Descrição<span class="text-danger">*</span></label>
                        <div class="col-sm-12">
                            <select name="description_id" id="description_id" class="form-control select2">
                                <option selected="selected" value="">Selecione uma opção</option>
                                @foreach ($description_releases as $description_release)
                                    <option value="{{$description_release->id}}">{{$description_release->id}} | {{$description_release->description}}</option>
                                @endforeach
                            </select>
                            {{-- span that show error message --}}
                            <span id="description_id-error" class="error invalid-feedback" style="display: none;"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="extra_info" class="col-sm-3 control-label">Comentário</label>
                        <div class="col-sm-12">
                            <input id="extra_info" name="extra_info" placeholder="Digite informação extra.." class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="amount" class="col-sm-3 control-label">Valor<span class="text-danger">*</span></label>
                        <div class="col-sm-12">
                            <input id="amount" name="amount" placeholder="R$ 0,00" class="form-control">
                            {{-- span that show error message --}}
                            <span id="amount-error" class="error invalid-feedback" style="display: none;"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="due_date" class="col-sm-3 control-label">Vencimento<span class="text-danger">*</span></label>
                        <div class="col-sm-12">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input id="due_date" name="due_date" class="form-control">
                                {{-- span that show error message --}}
                                <span id="due_date-error" class="error invalid-feedback" style="display: none;"></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="payment_date" class="col-sm-3 control-label">Pagamento</label>
                        <div class="col-sm-12">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input id="payment_date" name="payment_date" class="form-control">
                            </div>
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