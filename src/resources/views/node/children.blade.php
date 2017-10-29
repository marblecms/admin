 <div class="main-box">
    <header class="main-box-header clearfix">
        <h2>
            <div class="pull-left">{{trans('admin.children')}}</div>
            @if($node->class->allowChildren)
                <div class="pull-right">
                    <a href="{{url("admin/node/add/" . $node->id)}}" class="btn btn-info btn-xs">
                        {{trans('admin.add_children')}}
                    </a>
                </div>
            @endif
            <div class="clearfix"></div>
        </h2>
    </header>
    <div class="main-box-body clearfix">        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><a href="#"><span>Name</span></a></th>
                        <th class="text-right"><span>&nbsp;</span></th>
                    </tr>
                </thead>
                <tbody id="sortable-children"> 
                    @foreach($childNodes as $childNode) 
                        @continue( ! \App\PermissionHelper::allowedClass($childNode->class->id))
                        <tr data-node-id="{{$childNode->id}}">
                            <td>
                                <a href="{{ url("admin/node/edit/" . $childNode->id) }}"><i class="fa fa-{{$childNode->class->icon}}" ></i> {{$childNode->name}}</a>
                            </td>
                            <td class="text-right">
                                <div class="btn-group">
                                    <a href="{{ url("admin/node/edit/" . $childNode->id) }}" class="btn btn-xs btn-info">{{trans('admin.edit')}}</a>
                                    <a href="{{ url("admin/node/delete/" . $childNode->id) }}" onclick="return confirm('{{trans('admin.are_you_sure')}}');" class="btn btn-xs btn-danger">{{trans('admin.delete')}}</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if( count($childNodes) )
                <script>
                    $( "#sortable-children" ).sortable({
                        revert: true,
                        stop: function(){
                            var $childNodes = $("#sortable-children > tr"),
                                sortOrder = 0,
                                childNodes = {};

                            $childNodes.each(function(){
                                childNodes[$(this).data("node-id")] = sortOrder++;

                            });
                            
                            $.post("/admin/node/sort", {nodes:childNodes});
                        }
                    });
                </script>
            @else
                <center><i>{{trans('admin.no_children')}}</i></center>
                <br />
            @endif
        </div>
    </div>
</div>