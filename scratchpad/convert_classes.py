import os

REPLACEMENTS = [
    # Dynamic/conditional classes
    ("""class="{{ in_array($fila['inscripcion']->id, $clasificados) ? 'bg-emerald-50' : '' }}\"""",
     """class="{{ in_array($fila['inscripcion']->id, $clasificados) ? 'table-success' : '' }}\""""),
    ("""class="{{ request()->routeIs('publico.categorias.calendario') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-800' }}\"""",
     """class="{{ request()->routeIs('publico.categorias.calendario') ? 'link-primary fw-semibold' : 'link-secondary' }}\""""),
    ("""class="{{ request()->routeIs('publico.categorias.goleo') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-800' }}\"""",
     """class="{{ request()->routeIs('publico.categorias.goleo') ? 'link-primary fw-semibold' : 'link-secondary' }}\""""),
    ("""class="{{ request()->routeIs('publico.categorias.liguilla') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-800' }}\"""",
     """class="{{ request()->routeIs('publico.categorias.liguilla') ? 'link-primary fw-semibold' : 'link-secondary' }}\""""),
    ("""class="{{ request()->routeIs('publico.categorias.tabla') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-800' }}\"""",
     """class="{{ request()->routeIs('publico.categorias.tabla') ? 'link-primary fw-semibold' : 'link-secondary' }}\""""),
    ("""class="px-4 py-3 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1 {{ $esMiEquipo ? 'bg-indigo-50' : '' }}\"""",
     """class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-1 p-3 {{ $esMiEquipo ? 'bg-primary-subtle' : '' }}\""""),

    # Thead / table containers
    ('class="bg-gray-50"', 'class="table-light"'),
    ('class="bg-white overflow-hidden shadow-sm sm:rounded-lg"', 'class="bg-white shadow-sm rounded-3"'),
    ('class="min-w-full divide-y divide-gray-200 mt-6"', 'class="table table-hover align-middle mb-0 mt-4"'),
    ('class="min-w-full divide-y divide-gray-200"', 'class="table table-hover align-middle mb-0"'),
    ('class="divide-y divide-gray-200"', ''),
    ('class="overflow-x-auto"', 'class="table-responsive"'),

    # Cards
    ('class="bg-white p-4 shadow-sm sm:rounded-lg mb-4"', 'class="bg-white p-3 shadow-sm rounded-3 mb-3"'),
    ('class="bg-white p-4 shadow-sm sm:rounded-lg"', 'class="bg-white p-3 shadow-sm rounded-3"'),
    ('class="bg-white p-6 shadow-sm sm:rounded-lg flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3"',
     'class="bg-white p-4 shadow-sm rounded-3 d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-3"'),
    ('class="bg-white p-6 shadow-sm sm:rounded-lg text-gray-500"', 'class="bg-white p-4 shadow-sm rounded-3 text-secondary"'),
    ('class="bg-white p-6 shadow-sm sm:rounded-lg"', 'class="bg-white p-4 shadow-sm rounded-3"'),
    ('class="border border-gray-200 rounded-lg p-4"', 'class="border rounded-3 p-3"'),

    # Form selects (all confirmed <select> elements)
    ('class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"', 'class="form-select mt-1"'),
    ('class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"', 'class="form-select"'),
    ('class="block w-full sm:w-48 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"', 'class="form-select" style="max-width: 12rem;"'),
    ('class="block w-full sm:w-64 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"', 'class="form-select" style="max-width: 16rem;"'),
    ('class="flex-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"', 'class="form-select flex-grow-1"'),
    ('class="w-16 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"', 'class="form-select form-select-sm" style="max-width: 4rem;"'),
    ('class="w-32 text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"', 'class="form-select form-select-sm" style="max-width: 8rem;"'),
    ('class="w-48 text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"', 'class="form-select form-select-sm" style="max-width: 12rem;"'),
    ('class="w-full sm:w-28 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"', 'class="form-select" style="max-width: 7rem;"'),
    ('class="w-full sm:w-32 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"', 'class="form-select" style="max-width: 8rem;"'),
    ('class="w-full sm:w-36 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"', 'class="form-select" style="max-width: 9rem;"'),
    ('class="w-full sm:w-40 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"', 'class="form-select" style="max-width: 10rem;"'),
    ('class="w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"', 'class="form-select form-select-sm"'),

    # Checkboxes
    ('class="rounded border-gray-300 text-indigo-600 shadow-sm"', 'class="form-check-input"'),
    ('class="rounded border-gray-300 text-indigo-600"', 'class="form-check-input"'),

    # Plain inputs / misc widths
    ('class="block w-full max-w-sm"', 'class="mw-sm"'),
    ('class="block w-full"', ''),
    ('class="flex-1 w-full"', 'class="flex-grow-1"'),
    ('class="w-full sm:w-32"', 'style="max-width: 8rem;"'),
    ('class="mt-1 block w-full text-sm text-gray-700"', 'class="mt-1 small text-dark"'),
    ('class="mt-1 block w-full uppercase"', 'class="mt-1 text-uppercase"'),
    ('class="mt-1 block w-full"', 'class="mt-1"'),

    # Flex utilities
    ('class="flex flex-col sm:flex-row gap-3 items-end"', 'class="d-flex flex-column flex-sm-row gap-3 align-items-end"'),
    ('class="flex flex-col sm:flex-row gap-3 items-start"', 'class="d-flex flex-column flex-sm-row gap-3 align-items-start"'),
    ('class="flex flex-col sm:flex-row gap-3"', 'class="d-flex flex-column flex-sm-row gap-3"'),
    ('class="flex flex-wrap gap-3 text-sm"', 'class="d-flex flex-wrap gap-3 small"'),
    ('class="flex flex-wrap gap-4 text-sm border-b border-gray-200 pb-3"', 'class="d-flex flex-wrap gap-4 small border-bottom pb-3"'),
    ('class="flex gap-2"', 'class="d-flex gap-2"'),
    ('class="flex gap-3"', 'class="d-flex gap-3"'),
    ('class="flex items-center gap-2 text-sm text-gray-700"', 'class="d-flex align-items-center gap-2 small text-dark"'),
    ('class="flex items-center gap-2"', 'class="d-flex align-items-center gap-2"'),
    ('class="flex justify-between items-center mb-4"', 'class="d-flex justify-content-between align-items-center mb-3"'),
    ('class="flex justify-between items-center"', 'class="d-flex justify-content-between align-items-center"'),
    ('class="flex justify-end mt-6"', 'class="d-flex justify-content-end mt-4"'),
    ('class="flex justify-end"', 'class="d-flex justify-content-end"'),

    # Typography / headings
    ('class="font-medium text-gray-800 mb-2"', 'class="fw-medium text-dark mb-2"'),
    ('class="font-semibold text-gray-800 capitalize"', 'class="fw-semibold text-dark text-capitalize"'),
    ('class="font-semibold text-gray-800"', 'class="fw-semibold text-dark"'),
    ('class="font-semibold text-lg text-gray-800 mb-4"', 'class="h5 fw-semibold text-dark mb-3"'),
    ('class="font-semibold text-xl text-gray-800 leading-tight"', 'class="h4 fw-semibold text-dark mb-0"'),
    ('class="text-lg font-semibold text-gray-800"', 'class="h5 fw-semibold text-dark"'),
    ('class="text-2xl font-bold text-gray-900 mt-2"', 'class="h3 fw-bold text-dark mt-2"'),
    ('class="text-2xl font-bold text-gray-900 mt-3"', 'class="h3 fw-bold text-dark mt-3"'),

    # Grids
    ('class="grid grid-cols-1 sm:grid-cols-2 gap-4"', 'class="row g-3"'),
    ('class="grid grid-cols-2 gap-4 mt-4"', 'class="row g-3 mt-1"'),

    # Images
    ('class="h-10 w-10 object-cover rounded-full"', 'class="rounded-circle" style="width: 2.5rem; height: 2.5rem; object-fit: cover;"'),
    ('class="h-16 w-16 object-cover rounded-full mb-2"', 'class="rounded-circle mb-2" style="width: 4rem; height: 4rem; object-fit: cover;"'),

    # Misc layout
    ('class="inline"', 'class="d-inline"'),
    ('class="inline-block text-left"', 'class="d-inline-block text-start"'),
    ('class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"',
     'class="btn btn-dark btn-sm"'),
    ('class="list-disc list-inside"', ''),

    # Containers (max-w-*)
    ('class="max-w-2xl mx-auto sm:px-6 lg:px-8"', 'class="mw-2xl mx-auto px-3 px-lg-4"'),
    ('class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6"', 'class="mw-3xl mx-auto px-3 px-lg-4 d-flex flex-column gap-4"'),
    ('class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6"', 'class="mw-4xl mx-auto px-3 px-lg-4 d-flex flex-column gap-4"'),
    ('class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6"', 'class="mw-5xl mx-auto px-3 px-lg-4 d-flex flex-column gap-4"'),
    ('class="max-w-5xl mx-auto sm:px-6 lg:px-8"', 'class="mw-5xl mx-auto px-3 px-lg-4"'),
    ('class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4"', 'class="mw-6xl mx-auto px-3 px-lg-4 d-flex flex-column gap-3"'),
    ('class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6"', 'class="mw-6xl mx-auto px-3 px-lg-4 d-flex flex-column gap-4"'),
    ('class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4"', 'class="mw-7xl mx-auto px-3 px-lg-4 d-flex flex-column gap-3"'),
    ('class="max-w-7xl mx-auto sm:px-6 lg:px-8"', 'class="mw-7xl mx-auto px-3 px-lg-4"'),

    # Alerts
    ('class="mb-4 p-4 bg-green-100 text-green-800 rounded-md"', 'class="alert alert-success"'),
    ('class="p-4 bg-green-100 text-green-800 rounded-md"', 'class="alert alert-success"'),
    ('class="p-4 bg-red-100 text-red-800 rounded-md"', 'class="alert alert-danger"'),

    # Badges (status pills)
    ('class="ml-1 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800"', 'class="badge bg-danger-subtle text-danger-emphasis ms-1"'),
    ('class="mt-2 inline-block px-3 py-1 text-sm rounded-full bg-red-100 text-red-800"', 'class="badge bg-danger-subtle text-danger-emphasis mt-2"'),
    ('class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800"', 'class="badge bg-warning-subtle text-warning-emphasis"'),
    ('class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800"', 'class="badge bg-secondary-subtle text-secondary-emphasis"'),
    ('class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800"', 'class="badge bg-success-subtle text-success-emphasis"'),
    ('class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800"', 'class="badge bg-danger-subtle text-danger-emphasis"'),

    # Misc small helper panels
    ('class="mt-2 space-y-2 bg-gray-50 p-3 rounded-md"', 'class="mt-2 d-flex flex-column gap-2 bg-light p-3 rounded-3"'),
    ('class="space-y-4"', 'class="d-flex flex-column gap-3"'),

    # Spacing-only
    ('class="mt-1"', 'class="mt-1"'),
    ('class="mt-2"', 'class="mt-2"'),
    ('class="mt-3"', 'class="mt-3"'),
    ('class="mt-4 flex items-center"', 'class="mt-3 d-flex align-items-center"'),
    ('class="mt-4"', 'class="mt-3"'),
    ('class="p-4"', 'class="p-3"'),
    ('class="py-8"', 'class="py-4"'),

    # Table header cells
    ('class="px-4 py-1 pl-8 text-xs text-gray-500"', 'class="small text-secondary ps-4"'),
    ('class="px-4 py-1 text-right"', 'class="text-end"'),
    ('class="px-4 py-2 text-center font-semibold"', 'class="text-center fw-semibold"'),
    ('class="px-4 py-2 text-center text-gray-500"', 'class="text-center text-secondary"'),
    ('class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase"', 'class="small text-secondary text-uppercase text-center"'),
    ('class="px-4 py-2 text-center"', 'class="text-center"'),
    ('class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"', 'class="small text-secondary text-uppercase"'),
    ('class="px-4 py-2 whitespace-nowrap capitalize"', 'class="text-capitalize"'),
    ('class="px-4 py-2 whitespace-nowrap text-gray-500"', 'class="text-secondary"'),
    ('class="px-4 py-2 whitespace-nowrap text-right text-sm space-x-3"', 'class="text-end"'),
    ('class="px-4 py-2 whitespace-nowrap text-right text-sm"', 'class="text-end"'),
    ('class="px-4 py-2 whitespace-nowrap text-right"', 'class="text-end"'),
    ('class="px-4 py-2 whitespace-nowrap"', ''),
    ('class="px-4 py-2"', ''),
    ('class="px-4 py-3 bg-gray-50 border-b border-gray-200"', 'class="px-3 py-2 bg-light border-bottom"'),
    ('class="px-4 py-3 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1"', 'class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-1 p-3"'),
    ('class="px-6 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center"', 'class="px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center"'),
    ('class="px-6 py-3 bg-gray-50 border-b border-gray-200"', 'class="px-3 py-2 bg-light border-bottom"'),
    ('class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"', 'class="small text-secondary text-uppercase"'),
    ('class="px-6 py-3"', ''),
    ('class="px-6 py-4 text-center text-gray-500"', 'class="text-center text-secondary"'),
    ('class="px-6 py-4 whitespace-nowrap capitalize"', 'class="text-capitalize"'),
    ('class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3"', 'class="text-end"'),
    ('class="px-6 py-4 whitespace-nowrap text-right text-sm"', 'class="text-end"'),
    ('class="px-6 py-4 whitespace-nowrap"', ''),

    # Action links
    ('class="text-amber-600 hover:text-amber-900"', 'class="link-warning"'),
    ('class="text-emerald-600 hover:text-emerald-900 mr-3"', 'class="link-success me-3"'),
    ('class="text-emerald-600 hover:text-emerald-900"', 'class="link-success"'),
    ('class="text-gray-400"', 'class="text-muted"'),
    ('class="text-gray-500"', 'class="text-secondary"'),
    ('class="text-gray-600 hover:text-gray-900"', 'class="link-secondary"'),
    ('class="text-gray-700 mb-4"', 'class="text-dark mb-3"'),
    ('class="text-gray-700"', 'class="text-dark"'),
    ('class="text-indigo-600 hover:text-indigo-900 cursor-pointer text-sm"', 'class="link-primary small" style="cursor: pointer;"'),
    ('class="text-indigo-600 hover:text-indigo-900 text-sm"', 'class="link-primary small"'),
    ('class="text-indigo-600 hover:text-indigo-900"', 'class="link-primary"'),
    ('class="text-red-600 hover:text-red-900 cursor-pointer"', 'class="link-danger" style="cursor: pointer;"'),
    ('class="text-red-600 hover:text-red-900 text-sm"', 'class="link-danger small"'),
    ('class="text-red-600 hover:text-red-900"', 'class="link-danger"'),
    ('class="text-sm text-emerald-600 hover:text-emerald-900"', 'class="small link-success"'),
    ('class="text-sm text-gray-500 capitalize"', 'class="small text-secondary text-capitalize"'),
    ('class="text-sm text-gray-500 mb-4"', 'class="small text-secondary mb-3"'),
    ('class="text-sm text-gray-500 mt-1"', 'class="small text-secondary mt-1"'),
    ('class="text-sm text-gray-500 mt-2"', 'class="small text-secondary mt-2"'),
    ('class="text-sm text-gray-500"', 'class="small text-secondary"'),
    ('class="text-sm text-gray-600 hover:text-gray-900"', 'class="small link-secondary"'),
    ('class="text-sm text-red-600"', 'class="small text-danger"'),
    ('class="text-sm"', 'class="small"'),
    ('class="text-xs text-emerald-600 hover:text-emerald-900"', 'class="small link-success"'),
    ('class="text-xs text-gray-400 mt-1"', 'class="small text-muted mt-1"'),
    ('class="text-xs text-gray-400"', 'class="small text-muted"'),

    # Misc buttons inside details/forms
    ('class="w-full text-sm bg-indigo-600 text-white rounded-md py-1 hover:bg-indigo-700"', 'class="btn btn-primary btn-sm w-100"'),
    ('class="w-full text-sm bg-red-600 text-white rounded-md py-1 hover:bg-red-700"', 'class="btn btn-danger btn-sm w-100"'),

    # Capitalize helper left as-is (compatible meaning, but Tailwind-only token name) -> text-capitalize
    ('class="capitalize"', 'class="text-capitalize"'),
    ('class="ml-2 text-sm text-gray-700"', 'class="ms-2 small text-dark"'),
]

TARGET_DIRS = [
    "resources/views/admin",
    "resources/views/delegado",
    "resources/views/arbitro",
    "resources/views/publico",
    "resources/views/partidos",
]

def main():
    total_files = 0
    total_hits = 0
    for base in TARGET_DIRS:
        for root, _, files in os.walk(base):
            for fname in files:
                if not fname.endswith('.blade.php'):
                    continue
                path = os.path.join(root, fname)
                with open(path, 'r', encoding='utf-8') as f:
                    content = f.read()
                original = content
                file_hits = 0
                for old, new in REPLACEMENTS:
                    count = content.count(old)
                    if count:
                        file_hits += count
                        content = content.replace(old, new)
                if content != original:
                    with open(path, 'w', encoding='utf-8') as f:
                        f.write(content)
                    total_files += 1
                    total_hits += file_hits
                    print(f"{path}: {file_hits} replacements")
    print(f"\nTotal files changed: {total_files}, total replacements: {total_hits}")

if __name__ == '__main__':
    main()
