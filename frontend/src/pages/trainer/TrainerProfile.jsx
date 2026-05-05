import React, { useContext, useEffect, useState } from 'react'
import { AuthContext } from '@/context/AuthContext'
import { FormInput, Button, Card, LoadingSpinner, FormModal } from '@/components'
import api from '@/services/api'
import toast from 'react-hot-toast'

const TrainerProfile = () => {
  const { user } = useContext(AuthContext)
  const [loading, setLoading] = useState(true)
  const [trainer, setTrainer] = useState(null)
  const [form, setForm] = useState({ first_name: '', last_name: '', phone: '', specialization: '', hourly_rate: '' })
  const [certs, setCerts] = useState([])
  const [isCertOpen, setIsCertOpen] = useState(false)
  const [certForm, setCertForm] = useState({ cert_name: '', issuing_organization: '', cert_number: '', issue_date: '', expiry_date: '' })

  useEffect(() => {
    if (user?.trainer_id) fetchTrainer()
  }, [user?.trainer_id])

  const fetchTrainer = async () => {
    try {
      setLoading(true)
      const res = await api.trainersAPI.get(user.trainer_id)
      const data = res?.data?.data || res?.data || null
      setTrainer(data)
      setForm({
        first_name: data?.first_name || '',
        last_name: data?.last_name || '',
        phone: data?.phone || '',
        specialization: data?.specialization || '',
        hourly_rate: data?.hourly_rate || ''
      })
      setCerts(data?.certifications || [])
    } catch (e) {
      toast.error('Failed to load trainer profile')
    } finally {
      setLoading(false)
    }
  }

  const handleSubmit = async () => {
    try {
      setLoading(true)
      // include certifications when updating
        const payload = { ...form, certifications: certs }
        if (user?.role === 'trainer') {
          delete payload.hourly_rate
        }
        await api.trainersAPI.update(trainer.id, payload)
      toast.success('Profile updated')
      await fetchTrainer()
    } catch (e) {
      toast.error(e.response?.data?.message || 'Failed to update profile')
    } finally {
      setLoading(false)
    }
  }

  if (loading) return <div className="pt-20 min-h-screen bg-dark-bg flex items-center justify-center"><LoadingSpinner /></div>

  const handleAddCert = async () => {
    // create locally and send on save via certifications in update, or sync immediately
    try {
      setLoading(true)
      // append to certs array locally and push to server via update
      const next = [...certs, { ...certForm }]
      await api.trainersAPI.update(trainer.id, { certifications: next })
      setIsCertOpen(false)
      setCertForm({ cert_name: '', issuing_organization: '', cert_number: '', issue_date: '', expiry_date: '' })
      await fetchTrainer()
    } catch (e) {
      toast.error(e.response?.data?.message || 'Failed to add certification')
    } finally {
      setLoading(false)
    }
  }

  const handleRemoveCert = async (certId) => {
    try {
      setLoading(true)
      await api.trainersAPI.removeCertification(trainer.id, certId)
      toast.success('Certification removed')
      await fetchTrainer()
    } catch (e) {
      toast.error(e.response?.data?.message || 'Failed to remove certification')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="pt-20 min-h-screen bg-dark-bg pb-12">
      <div className="max-w-3xl mx-auto px-4 py-8">
        <h1 className="text-3xl font-bold text-white mb-4">My Profile</h1>
        <Card>
          <FormInput label="First Name" value={form.first_name} onChange={(e) => setForm({ ...form, first_name: e.target.value })} required />
          <FormInput label="Last Name" value={form.last_name} onChange={(e) => setForm({ ...form, last_name: e.target.value })} required />
          <FormInput label="Phone" value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
          <FormInput label="Specialization" value={form.specialization} onChange={(e) => setForm({ ...form, specialization: e.target.value })} />
            <FormInput label="Hourly Rate" type="number" value={form.hourly_rate} onChange={(e) => setForm({ ...form, hourly_rate: e.target.value })} disabled={user?.role === 'trainer'} />
          <div className="flex justify-end mt-4">
            <Button onClick={handleSubmit}>Save</Button>
          </div>
        </Card>

        {/* Certifications */}
        <div className="mt-6">
          <h2 className="text-xl font-bold text-white mb-3">Certifications</h2>
          <Card>
            {certs.length === 0 ? (
              <div className="p-6 text-gray-400">No certifications added yet.</div>
            ) : (
              <ul className="space-y-3">
                {certs.map(c => (
                  <li key={c.id || c.cert_name} className="flex items-center justify-between">
                    <div>
                      <div className="text-white font-semibold">{c.cert_name}</div>
                      <div className="text-sm text-gray-400">{c.issuing_organization || ''} {c.cert_number ? `· ${c.cert_number}` : ''}</div>
                    </div>
                    <div>
                      <button onClick={() => handleRemoveCert(c.id)} className="text-sm text-red-400 hover:underline">Remove</button>
                    </div>
                  </li>
                ))}
              </ul>
            )}
            <div className="mt-4 flex justify-end">
              <Button onClick={() => setIsCertOpen(true)}>Add Certification</Button>
            </div>
          </Card>
        </div>

        <FormModal isOpen={isCertOpen} title="Add Certification" onClose={() => setIsCertOpen(false)} onSubmit={handleAddCert} submitLabel="Add">
          <FormInput label="Certification Name" value={certForm.cert_name} onChange={(e) => setCertForm({ ...certForm, cert_name: e.target.value })} required />
          <FormInput label="Issuing Organization" value={certForm.issuing_organization} onChange={(e) => setCertForm({ ...certForm, issuing_organization: e.target.value })} />
          <FormInput label="Certificate Number" value={certForm.cert_number} onChange={(e) => setCertForm({ ...certForm, cert_number: e.target.value })} />
          <FormInput label="Issue Date" type="date" value={certForm.issue_date} onChange={(e) => setCertForm({ ...certForm, issue_date: e.target.value })} />
          <FormInput label="Expiry Date" type="date" value={certForm.expiry_date} onChange={(e) => setCertForm({ ...certForm, expiry_date: e.target.value })} />
        </FormModal>
      </div>
    </div>
  )
}

export default TrainerProfile
