import re

with open('app/Filament/Resources/CallerResource.php', 'r') as f:
    content = f.read()

if 'use Filament\\Tables\\Actions\\BulkActionGroup;' not in content:
    content = content.replace('use Filament\\Tables\\Table;', 'use Filament\\Tables\\Table;\nuse Filament\\Tables\\Actions\\BulkActionGroup;\nuse Filament\\Tables\\Actions\\BulkAction;\nuse Filament\\Tables\\Actions\\DeleteBulkAction;')

# Simple replace approach instead of regex with complex backreferences
start_str = "            ])\n\n            ->actions([\n                ActionGroup::make([\n                    // Export Actions"
end_str = "                        ->deselectRecordsAfterCompletion(),\n                ])\n                    ->icon('heroicon-o-ellipsis-horizontal')\n                    ->tooltip('إجراءات'),\n            ])"

if start_str in content and end_str in content:
    # Extract the stuff in between
    start_idx = content.find(start_str)
    end_idx = content.find(end_str) + len(end_str)

    old_section = content[start_idx:end_idx]

    new_section = """            ])
            ->actions([
                \\Filament\\Tables\\Actions\\EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // Export Actions
                    BulkAction::make('exportCsv')
                        ->label('تصدير CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function ($records) {
                            return static::exportToCsv($records);
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('exportExcel')
                        ->label('تصدير Excel')
                        ->icon('heroicon-o-table-cells')
                        ->color('success')
                        ->action(function ($records) {
                            return static::exportToExcel($records);
                        })
                        ->deselectRecordsAfterCompletion(),

                    // Status Management
                    BulkAction::make('changeStatus')
                        ->label('تغيير الحالة')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            \\Filament\\Forms\\Components\\Select::make('status')
                                ->label('الحالة الجديدة')
                                ->options([
                                    'active' => 'نشط',
                                    'inactive' => 'غير نشط',
                                    'selected' => 'مُختار',
                                    'blocked' => 'محظور',
                                ])
                                ->required()
                                ->native(false),
                        ])
                        ->action(function (array $data, $records): void {
                            $records->each(function ($record) use ($data): void {
                                $record->update(['status' => $data['status']]);
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading('تغيير حالة المتصلين')
                        ->modalDescription('سيتم تغيير حالة جميع المتصلين المحددين')
                        ->deselectRecordsAfterCompletion(),

                    // Delete Action
                    DeleteBulkAction::make()
                        ->label('حذف المحدد'),

                    // Mark Selected as Winners (manual confirmation)
                    BulkAction::make('markAsWinners')
                        ->label('تأكيد كفائزين')
                        ->icon('heroicon-o-trophy')
                        ->color('success')
                        ->action(function ($records): void {
                            $records->each(function ($record): void {
                                $record->update([
                                    'is_winner' => true,
                                    'is_selected' => true, // also ensure they're marked selected
                                ]);
                            });

                            \\Filament\\Notifications\\Notification::make()
                                ->success()
                                ->title('تم التحديث')
                                ->body('تم تأكيد '.$records->count().' متصل كفائزين')
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('تأكيد الفائزين')
                        ->modalDescription('سيتم تأكيد المتصلين المحددين كفائزين نهائيين.')
                        ->deselectRecordsAfterCompletion(),

                    // Remove Winner Status (keeps selected)
                    BulkAction::make('removeWinnerStatus')
                        ->label('إزالة حالة الفوز')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function ($records): void {
                            $records->each(function ($record): void {
                                $record->update(['is_winner' => false]);
                            });

                            \\Filament\\Notifications\\Notification::make()
                                ->success()
                                ->title('تم التحديث')
                                ->body('تم إزالة حالة الفوز من '.$records->count().' متصل (لا يزالون مُختارين)')
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    // Reset Selection (allow them back into draws)
                    BulkAction::make('resetSelection')
                        ->label('إعادة إلى السحب')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('gray')
                        ->action(function ($records): void {
                            $records->each(function ($record): void {
                                $record->update([
                                    'is_selected' => false,
                                    'is_winner' => false,
                                    'status' => 'active',
                                ]);
                            });

                            \\Filament\\Notifications\\Notification::make()
                                ->success()
                                ->title('تم التحديث')
                                ->body('تم إعادة '.$records->count().' متصل إلى قائمة السحب')
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('إعادة إلى السحب')
                        ->modalDescription('سيتم إزالة حالة الاختيار والفوز وإعادتهم كمتصلين نشطين مؤهلين للسحب.')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])"""

    content = content[:start_idx] + new_section + content[end_idx:]

with open('app/Filament/Resources/CallerResource.php', 'w') as f:
    f.write(content)
