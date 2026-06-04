{{--
    Single reason card. Used both for existing reasons (foreach) and as JS template
    (with __INDEX__ placeholder).

    Variables: $r (array with k/l/module_set/module_title/req/custom_modules/custom_statuses), $i (index)
--}}
<div class="ph-reason-card mb-3 p-3" style="border:1px solid var(--ph-border);border-radius:14px;background:#fafafa;">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div style="flex:1;display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;">
            <div>
                <label class="text-muted small font-weight-bold">Ключ (k)</label>
                <input type="text" name="reasons[{{ $i }}][k]" value="{{ is_array($r) ? ($r['k'] ?? '') : '' }}"
                    class="form-control form-control-sm" placeholder="bug" pattern="[a-z0-9_\-]{1,64}" required>
            </div>
            <div>
                <label class="text-muted small font-weight-bold">Название (l)</label>
                <input type="text" name="reasons[{{ $i }}][l]" value="{{ is_array($r) ? ($r['l'] ?? '') : '' }}"
                    class="form-control form-control-sm" placeholder="Баг" required>
            </div>
            <div>
                <label class="text-muted small font-weight-bold">Источник модулей</label>
                <select name="reasons[{{ $i }}][module_set]" class="form-control form-control-sm" onchange="toggleCustom(this)">
                    @php $ms = is_array($r) ? ($r['module_set'] ?? 'common') : 'common'; @endphp
                    <option value="common"  {{ $ms==='common'?'selected':'' }}>common — общие модули</option>
                    <option value="payment" {{ $ms==='payment'?'selected':'' }}>payment — темы оплаты</option>
                    <option value="custom"  {{ $ms==='custom'?'selected':'' }}>custom — свои списки</option>
                </select>
            </div>
            <div>
                <label class="text-muted small font-weight-bold">Заголовок поля</label>
                <input type="text" name="reasons[{{ $i }}][module_title]"
                    value="{{ is_array($r) ? ($r['module_title'] ?? 'Модули') : 'Модули' }}"
                    class="form-control form-control-sm" placeholder="Модули">
            </div>
            <div>
                <label class="text-muted small font-weight-bold d-block">Комментарий</label>
                <label class="d-flex align-items-center mt-1" style="gap:6px;font-weight:normal;cursor:pointer;">
                    <input type="checkbox" name="reasons[{{ $i }}][req]" value="1"
                        {{ (is_array($r) && !empty($r['req'])) ? 'checked' : '' }}>
                    <span class="small">Обязателен</span>
                </label>
            </div>
        </div>
        <button type="button" class="btn btn-light btn-sm ml-2" onclick="removeReason(this)" title="Удалить причину">
            <i class="fas fa-trash text-danger"></i>
        </button>
    </div>

    @php
        $isCustom = is_array($r) && (($r['module_set'] ?? 'common') === 'custom');
        $cm = is_array($r) && !empty($r['custom_modules']) ? implode("\n", (array)$r['custom_modules']) : '';
        $cs = is_array($r) && !empty($r['custom_statuses']) ? implode("\n", (array)$r['custom_statuses']) : '';
    @endphp
    <div class="ph-custom-box" style="display:{{ $isCustom ? 'block' : 'none' }};margin-top:8px;">
        <div class="row">
            <div class="col-md-6">
                <label class="text-muted small font-weight-bold">Свои модули (по строке)</label>
                <textarea name="reasons[{{ $i }}][custom_modules]" rows="4" class="form-control form-control-sm" style="font-family:monospace;font-size:.85rem;">{{ $cm }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="text-muted small font-weight-bold">Свои статусы (по строке, опционально)</label>
                <textarea name="reasons[{{ $i }}][custom_statuses]" rows="4" class="form-control form-control-sm" style="font-family:monospace;font-size:.85rem;">{{ $cs }}</textarea>
            </div>
        </div>
    </div>
</div>
