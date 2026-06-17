@extends('layouts.app')

@section('content')
<div class="page-container animate-fade-up" style="max-width:680px;">

    {{-- Header --}}
    <div style="display:flex; align-items:center; gap:14px; margin-bottom:28px;">
        <a href="{{ route('employees.index') }}" class="btn-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="page-title">Add Employee</h1>
            <p class="page-subtitle">Register a new engineer in the company directory.</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="form-section">
        <form action="{{ route('employees.store') }}" method="POST" style="display:flex; flex-direction:column; gap:22px;">
            @csrf

            {{-- Name --}}
            <div>
                <label for="name" class="form-label">Full Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    placeholder="e.g. Linus Torvalds"
                    class="form-input">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    placeholder="e.g. linus@linuxfoundation.org"
                    class="form-input">
                @error('email')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Department & Role --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                <div>
                    <label for="department" class="form-label">Department</label>
                    <select name="department" id="department" required class="form-input" style="cursor:pointer;">
                        <option value="Engineering" {{ old('department') == 'Engineering' ? 'selected' : '' }}>Engineering</option>
                        <option value="Product"     {{ old('department') == 'Product'     ? 'selected' : '' }}>Product</option>
                        <option value="Design"      {{ old('department') == 'Design'      ? 'selected' : '' }}>Design</option>
                        <option value="QA"          {{ old('department') == 'QA'          ? 'selected' : '' }}>QA</option>
                        <option value="DevOps"      {{ old('department') == 'DevOps'      ? 'selected' : '' }}>DevOps</option>
                    </select>
                    @error('department')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="role" class="form-label">Job Title</label>
                    <input type="text" name="role" id="role" value="{{ old('role') }}" required
                        placeholder="e.g. Senior Backend Engineer"
                        class="form-input">
                    @error('role')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- GitHub Username --}}
            <div>
                <label for="github_username" class="form-label">GitHub Username <span style="color:#334155; font-weight:400; text-transform:none; letter-spacing:0;">(optional)</span></label>
                <div style="position:relative;">
                    <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); font-size:13px; color:var(--text-muted); font-weight:600; pointer-events:none; user-select:none; font-family:monospace;">github.com/</span>
                    <input type="text" name="github_username" id="github_username" value="{{ old('github_username') }}"
                        placeholder="torvalds"
                        class="form-input" style="padding-left:108px;">
                </div>
                @error('github_username')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Divider --}}
            <div class="divider" style="margin:4px 0;"></div>

            {{-- Buttons --}}
            <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                <a href="{{ route('employees.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" style="cursor:pointer; font-family:inherit;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Register Developer
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
