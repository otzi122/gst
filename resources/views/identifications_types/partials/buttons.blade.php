@can('tipos identificadores:actualizar')
  <button class="btn btn-warning btn_edit" title="Editar" data-item="{{ $id }}"><i class="fa fa-edit"></i></button>
@endcan
@if( $id != 1 ) 
  @can('tipos identificadores:eliminar')
    <button class="btn btn-danger btn_del" title="Eliminar" data-route="{{ route( 'id_types.destroy', $id )}}"><i class="fa fa-trash"></i></button>
  @endcan
@endif