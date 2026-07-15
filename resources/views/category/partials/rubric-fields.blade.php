@php
    $stageLabels = \App\Models\ReviewRubric::stageLabels();
    $rubricInput = old('rubrics');

    if (!$rubricInput && isset($category)) {
        $rubricInput = [];

        foreach ($category->rubrics as $rubric) {
            $rubricInput[$rubric->stage]['groups'] = $rubric->groups->map(function ($group) {
                return [
                    'title' => $group->title,
                    'weight' => $group->weight,
                    'sort_order' => $group->sort_order,
                    'items' => $group->items->map(function ($item) {
                        return [
                            'title' => $item->title,
                            'description' => $item->description,
                            'weight' => $item->weight,
                            'sort_order' => $item->sort_order,
                        ];
                    })->toArray(),
                ];
            })->toArray();
        }
    }

    $emptyGroup = [
        'title' => '',
        'weight' => '',
        'sort_order' => 0,
        'items' => [
            [
                'title' => '',
                'description' => '',
                'weight' => 1,
                'sort_order' => 0,
            ],
        ],
    ];
@endphp

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Rubrik Penilaian</h3>
    </div>
    <div class="box-body">
        <div class="nav-tabs-custom" style="margin-bottom:0;">
            <ul class="nav nav-tabs">
                @foreach($stageLabels as $stage => $label)
                <li class="{{ $loop->first ? 'active' : '' }}">
                    <a href="#rubric-{{ $stage }}" data-toggle="tab">{{ $label }}</a>
                </li>
                @endforeach
            </ul>
            <div class="tab-content">
                @foreach($stageLabels as $stage => $label)
                @php
                    $groups = data_get($rubricInput, $stage . '.groups', []);
                    $groups = count($groups) ? array_values($groups) : [$emptyGroup];
                @endphp
                <div class="tab-pane {{ $loop->first ? 'active' : '' }} rubric-stage" id="rubric-{{ $stage }}" data-stage="{{ $stage }}" data-next-group-index="{{ count($groups) }}">
                    <input type="hidden" name="rubrics[{{ $stage }}][present]" value="1">

                    <div class="rubric-groups">
                        @foreach($groups as $groupIndex => $group)
                        @php
                            $items = data_get($group, 'items', []);
                            $items = count($items) ? array_values($items) : [$emptyGroup['items'][0]];
                        @endphp
                        <div class="rubric-group" data-group-index="{{ $groupIndex }}" data-next-item-index="{{ count($items) }}" style="border:1px solid #d2d6de; padding:15px; margin-bottom:15px;">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Aspek Utama</label>
                                        <input type="text" class="form-control" name="rubrics[{{ $stage }}][groups][{{ $groupIndex }}][title]" value="{{ data_get($group, 'title') }}" placeholder="NARASI & SKENARIO">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Bobot Utama</label>
                                        <input type="number" class="form-control" name="rubrics[{{ $stage }}][groups][{{ $groupIndex }}][weight]" value="{{ data_get($group, 'weight') }}" min="0" step="0.01" placeholder="50">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Urutan</label>
                                        <input type="number" class="form-control" name="rubrics[{{ $stage }}][groups][{{ $groupIndex }}][sort_order]" value="{{ data_get($group, 'sort_order', $groupIndex) }}" min="0">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group" style="padding-top:25px;">
                                        <button type="button" class="btn btn-danger btn-block remove-rubric-group">Hapus</button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered rubric-items">
                                    <thead>
                                        <tr>
                                            <th style="width:22%;">Sub-Aspek</th>
                                            <th>Indikator</th>
                                            <th style="width:12%;">Bobot</th>
                                            <th style="width:12%;">Urutan</th>
                                            <th style="width:10%;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $itemIndex => $item)
                                        <tr class="rubric-item">
                                            <td>
                                                <input type="text" class="form-control" name="rubrics[{{ $stage }}][groups][{{ $groupIndex }}][items][{{ $itemIndex }}][title]" value="{{ data_get($item, 'title') }}" placeholder="Orisinalitas dan Tema">
                                            </td>
                                            <td>
                                                <textarea class="form-control" name="rubrics[{{ $stage }}][groups][{{ $groupIndex }}][items][{{ $itemIndex }}][description]" rows="2">{{ data_get($item, 'description') }}</textarea>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="rubrics[{{ $stage }}][groups][{{ $groupIndex }}][items][{{ $itemIndex }}][weight]" value="{{ data_get($item, 'weight', 1) }}" min="0" step="0.01">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="rubrics[{{ $stage }}][groups][{{ $groupIndex }}][items][{{ $itemIndex }}][sort_order]" value="{{ data_get($item, 'sort_order', $itemIndex) }}" min="0">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-xs remove-rubric-item">Hapus</button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <button type="button" class="btn btn-default btn-sm add-rubric-item">Tambah Sub-Aspek</button>
                        </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-info btn-sm add-rubric-group">Tambah Aspek</button>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="rubric-group-template">
    <div class="rubric-group" data-group-index="__GROUP__" data-next-item-index="1" style="border:1px solid #d2d6de; padding:15px; margin-bottom:15px;">
        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label>Aspek Utama</label>
                    <input type="text" class="form-control" name="rubrics[__STAGE__][groups][__GROUP__][title]" placeholder="NARASI & SKENARIO">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Bobot Utama</label>
                    <input type="number" class="form-control" name="rubrics[__STAGE__][groups][__GROUP__][weight]" min="0" step="0.01" placeholder="50">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Urutan</label>
                    <input type="number" class="form-control" name="rubrics[__STAGE__][groups][__GROUP__][sort_order]" value="__GROUP__" min="0">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group" style="padding-top:25px;">
                    <button type="button" class="btn btn-danger btn-block remove-rubric-group">Hapus</button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered rubric-items">
                <thead>
                    <tr>
                        <th style="width:22%;">Sub-Aspek</th>
                        <th>Indikator</th>
                        <th style="width:12%;">Bobot</th>
                        <th style="width:12%;">Urutan</th>
                        <th style="width:10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    __ITEM__
                </tbody>
            </table>
        </div>

        <button type="button" class="btn btn-default btn-sm add-rubric-item">Tambah Sub-Aspek</button>
    </div>
</script>

<script type="text/template" id="rubric-item-template">
    <tr class="rubric-item">
        <td>
            <input type="text" class="form-control" name="rubrics[__STAGE__][groups][__GROUP__][items][__ITEM__][title]" placeholder="Orisinalitas dan Tema">
        </td>
        <td>
            <textarea class="form-control" name="rubrics[__STAGE__][groups][__GROUP__][items][__ITEM__][description]" rows="2"></textarea>
        </td>
        <td>
            <input type="number" class="form-control" name="rubrics[__STAGE__][groups][__GROUP__][items][__ITEM__][weight]" value="1" min="0" step="0.01">
        </td>
        <td>
            <input type="number" class="form-control" name="rubrics[__STAGE__][groups][__GROUP__][items][__ITEM__][sort_order]" value="__ITEM__" min="0">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-xs remove-rubric-item">Hapus</button>
        </td>
    </tr>
</script>

@push('scripts')
<script>
    (function () {
        function renderTemplate(templateId, replacements) {
            var html = document.getElementById(templateId).innerHTML;

            Object.keys(replacements).forEach(function (key) {
                html = html.replace(new RegExp(key, 'g'), replacements[key]);
            });

            return html;
        }

        document.addEventListener('click', function (event) {
            if (event.target.classList.contains('add-rubric-group')) {
                var stagePane = event.target.closest('.rubric-stage');
                var stage = stagePane.getAttribute('data-stage');
                var groupIndex = parseInt(stagePane.getAttribute('data-next-group-index'), 10);
                var itemHtml = renderTemplate('rubric-item-template', {
                    '__STAGE__': stage,
                    '__GROUP__': groupIndex,
                    '__ITEM__': 0
                });
                var groupHtml = renderTemplate('rubric-group-template', {
                    '__STAGE__': stage,
                    '__GROUP__': groupIndex,
                    '__ITEM__': itemHtml
                });

                stagePane.querySelector('.rubric-groups').insertAdjacentHTML('beforeend', groupHtml);
                stagePane.setAttribute('data-next-group-index', groupIndex + 1);
            }

            if (event.target.classList.contains('add-rubric-item')) {
                var group = event.target.closest('.rubric-group');
                var stage = event.target.closest('.rubric-stage').getAttribute('data-stage');
                var groupIndex = group.getAttribute('data-group-index');
                var itemIndex = parseInt(group.getAttribute('data-next-item-index'), 10);
                var itemHtml = renderTemplate('rubric-item-template', {
                    '__STAGE__': stage,
                    '__GROUP__': groupIndex,
                    '__ITEM__': itemIndex
                });

                group.querySelector('.rubric-items tbody').insertAdjacentHTML('beforeend', itemHtml);
                group.setAttribute('data-next-item-index', itemIndex + 1);
            }

            if (event.target.classList.contains('remove-rubric-group')) {
                event.target.closest('.rubric-group').remove();
            }

            if (event.target.classList.contains('remove-rubric-item')) {
                event.target.closest('.rubric-item').remove();
            }
        });
    })();
</script>
@endpush
