@extends('layouts.app')

@section('content')
<div class="page-container animate-fade-up" style="max-width:680px;">

    {{-- Header --}}
    <div style="display:flex; align-items:center; gap:14px; margin-bottom:28px;">
        <a href="{{ route('employees.index') }}" class="btn-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="page-title">Edit Developer</h1>
            <p class="page-subtitle">Modify account details for <strong style="color:#a5b4fc;">{{ $employee->name }}</strong>.</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="form-section">
        <form action="{{ route('employees.update', $employee) }}" method="POST" style="display:flex; flex-direction:column; gap:22px;">
            @csrf
            @method('PUT')

            {{-- Name --}}
            <div>
                <label for="name" class="form-label">Full Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $employee->name) }}" required
                    class="form-input">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email', $employee->email) }}" required
                    class="form-input">
                @error('email')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Department & Role --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                <div>
                    <label for="department" class="form-label">Department</label>
                    <select name="department" id="department" required class="form-input" style="cursor:pointer;">
                        <option value="Engineering" {{ old('department', $employee->department) == 'Engineering' ? 'selected' : '' }}>Engineering</option>
                        <option value="Product"     {{ old('department', $employee->department) == 'Product'     ? 'selected' : '' }}>Product</option>
                        <option value="Design"      {{ old('department', $employee->department) == 'Design'      ? 'selected' : '' }}>Design</option>
                        <option value="QA"          {{ old('department', $employee->department) == 'QA'          ? 'selected' : '' }}>QA</option>
                        <option value="DevOps"      {{ old('department', $employee->department) == 'DevOps'      ? 'selected' : '' }}>DevOps</option>
                    </select>
                    @error('department')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="role" class="form-label">Job Title</label>
                    <input type="text" name="role" id="role" value="{{ old('role', $employee->role) }}" required
                        class="form-input">
                    @error('role')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- VCS Usernames --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                {{-- GitHub Username --}}
                <div>
                    <label for="github_username" class="form-label">GitHub Username <span style="color:#334155; font-weight:400; text-transform:none; letter-spacing:0;">(optional)</span></label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); font-size:13px; color:var(--text-muted); font-weight:600; pointer-events:none; user-select:none; font-family:monospace;">github.com/</span>
                        <input type="text" name="github_username" id="github_username" value="{{ old('github_username', $employee->github_username) }}"
                            class="form-input" style="padding-left:108px;">
                    </div>
                    @error('github_username')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- GitLab Username --}}
                @php
                    $gitlabHost = parse_url(config('services.gitlab.url', 'https://gitlab.com'), PHP_URL_HOST) ?: 'gitlab.com';
                    $gitlabPrefix = $gitlabHost . '/';
                    $gitlabPadding = 14 + (strlen($gitlabPrefix) * 8.2);
                @endphp
                <div>
                    <label for="gitlab_username" class="form-label">GitLab Username <span style="color:#334155; font-weight:400; text-transform:none; letter-spacing:0;">(optional)</span></label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); font-size:13px; color:var(--text-muted); font-weight:600; pointer-events:none; user-select:none; font-family:monospace;">{{ $gitlabPrefix }}</span>
                        <input type="text" name="gitlab_username" id="gitlab_username" value="{{ old('gitlab_username', $employee->gitlab_username) }}"
                            placeholder="Leave blank to use GitHub username as fallback"
                            class="form-input" style="padding-left:{{ $gitlabPadding }}px;">
                    </div>
                    @error('gitlab_username')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="divider" style="margin:4px 0;"></div>

            {{-- Buttons --}}
            <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                <a href="{{ route('employees.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" style="cursor:pointer; font-family:inherit;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Save Changes
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
